use crate::models::*;
use crate::rows::*;
use anyhow::Result;
use async_trait::async_trait;
use sqlx::{postgres::PgRow, PgPool, Row};
use std::collections::HashMap;

const WORKOUT_FULL_QUERY: &str = "
    SELECT 
        w.id as workout_id, w.name as workout_name, w.description as workout_description, w.user_id,
        we.id as workout_exercise_id, we.exercise_order,
        e.id as exercise_id, e.name as exercise_name, e.description as exercise_description, e.is_personal,
        ws.id as set_id, ws.set_order
    FROM workout w
    LEFT JOIN workout_exercise we ON we.workout_id = w.id
    LEFT JOIN exercise e ON e.id = we.exercise_id
    LEFT JOIN workout_set ws ON ws.workout_exercise_id = we.id
";

/// Trait for workout data access operations
#[async_trait]
pub trait WorkoutRepository {
    async fn get_workout_by_id(&self, workout_id: i32) -> Result<Workout>;
    async fn get_all_workouts_for_user(&self, user_id: i32) -> Result<Vec<Workout>>;
    async fn get_all_exercises_for_user(&self, id: i32) -> Result<Vec<Exercise>>;
    async fn get_exercise_by_id(&self, exercise_id: i32) -> Result<Exercise>;
    async fn get_workout_sets(&self, workout_exercise_id: i32) -> Result<Vec<WorkoutSet>>;
    async fn create_workout(&self, workout: &Workout) -> Result<()>;
    async fn insert_workout_session(
        &self,
        user_id: i32,
        workout_session: &WorkoutSession,
    ) -> Result<()>;
}

/// PostgreSQL implementation of WorkoutRepository
#[derive(Debug)]
pub struct PostgresWorkoutRepository {
    pool: PgPool,
}

impl PostgresWorkoutRepository {
    pub fn new(pool: PgPool) -> Self {
        Self { pool }
    }
}

#[async_trait]
impl WorkoutRepository for PostgresWorkoutRepository {
    async fn get_workout_by_id(&self, workout_id: i32) -> Result<Workout> {
        let query = format!(
            "{} WHERE w.id = $1 ORDER BY we.exercise_order, ws.set_order",
            WORKOUT_FULL_QUERY
        );

        let rows = sqlx::query(&query)
            .bind(workout_id)
            .fetch_all(&self.pool)
            .await?;

        if rows.is_empty() {
            anyhow::bail!("Workout {} not found", workout_id);
        }

        Ok(assemble_workouts(&rows).remove(0))
    }

    async fn insert_workout_session(
        &self,
        user_id: i32,
        workout_session: &WorkoutSession,
    ) -> Result<()> {
        let mut tx = self.pool.begin().await?;

        if workout_session.user_id != user_id {
            anyhow::bail!(
                "path user_id {} does not match workout_session.user_id {}",
                user_id,
                workout_session.user_id
            );
        }

        let session_id: i32 = sqlx::query_scalar(
            "INSERT INTO workout_session (user_id, workout_id, start_time, end_time, notes)
             VALUES ($1, $2, $3, $4, $5)
             RETURNING id",
        )
        .bind(workout_session.user_id)
        .bind(workout_session.workout_id)
        .bind(workout_session.start_time)
        .bind(workout_session.end_time)
        .bind(&workout_session.notes)
        .fetch_one(&mut *tx)
        .await?;

        for exercise_log in &workout_session.logged_exercises {
            for set_log in &exercise_log.sets {
                sqlx::query(
                    "INSERT INTO set_log (session_id, exercise_id, workout_set_id, weight, reps, note)
                     VALUES ($1, $2, $3, $4, $5, $6)",
                )
                .bind(session_id)
                .bind(exercise_log.exercise.id)
                .bind(set_log.workout_set_id)
                .bind(set_log.weight)
                .bind(set_log.reps)
                .bind(&set_log.note)
                .execute(&mut *tx)
                .await?;
            }
        }

        tx.commit().await?;
        Ok(())
    }

    async fn get_all_workouts_for_user(&self, user_id: i32) -> Result<Vec<Workout>> {
        let query = format!(
            "{} WHERE w.user_id = $1 ORDER BY w.id, we.exercise_order, ws.set_order",
            WORKOUT_FULL_QUERY
        );

        let rows = sqlx::query(&query)
            .bind(user_id)
            .fetch_all(&self.pool)
            .await?;

        Ok(assemble_workouts(&rows))
    }

    async fn get_all_exercises_for_user(&self, id: i32) -> Result<Vec<Exercise>> {
        let rows = sqlx::query_as::<_, ExerciseRow>(
            "SELECT e.id, e.name, e.description, e.is_personal
             FROM exercise e
             WHERE e.id IN (SELECT exercise_id FROM general_exercises)
             OR e.id IN (SELECT exercise_id FROM user_exercises WHERE user_id = $1)
             ORDER BY id ASC",
        )
        .bind(id)
        .fetch_all(&self.pool)
        .await?;

        Ok(assemble_exercises(&rows))
    }

    async fn get_exercise_by_id(&self, exercise_id: i32) -> Result<Exercise> {
        let row = sqlx::query_as::<_, ExerciseRow>(
            "SELECT id, name, description, is_personal FROM exercise WHERE id = $1",
        )
        .bind(exercise_id)
        .fetch_one(&self.pool)
        .await?;

        Ok(Exercise {
            id: row.id,
            name: row.name,
            description: row.description.unwrap_or_default(),
            is_personal: row.is_personal,
        })
    }

    async fn get_workout_sets(&self, workout_exercise_id: i32) -> Result<Vec<WorkoutSet>> {
        let rows = sqlx::query_as::<_, WorkoutSetRow>(
            "SELECT id, workout_exercise_id, set_order 
             FROM workout_set 
             WHERE workout_exercise_id = $1 
             ORDER BY set_order ASC",
        )
        .bind(workout_exercise_id)
        .fetch_all(&self.pool)
        .await?;

        Ok(rows
            .into_iter()
            .map(|r| WorkoutSet {
                id: r.id,
                set_order: r.set_order,
            })
            .collect())
    }

    async fn create_workout(&self, workout: &Workout) -> Result<()> {
        let mut tx = self.pool.begin().await?;

        let workout_id: i32 = sqlx::query_scalar(
            "INSERT INTO workout (name, description, user_id) 
             VALUES ($1, $2, $3) 
             RETURNING id",
        )
        .bind(&workout.name)
        .bind(&workout.description)
        .bind(workout.user_id)
        .fetch_one(&mut *tx)
        .await?;

        for exercise in &workout.exercises {
            let workout_exercise_id: i32 = sqlx::query_scalar(
                "INSERT INTO workout_exercise (workout_id, exercise_id, exercise_order) 
                 VALUES ($1, $2, $3) 
                 RETURNING id",
            )
            .bind(workout_id)
            .bind(exercise.exercise.id)
            .bind(exercise.order_index)
            .fetch_one(&mut *tx)
            .await?;

            for set in &exercise.sets {
                sqlx::query(
                    "INSERT INTO workout_set (workout_exercise_id, set_order) 
                     VALUES ($1, $2)",
                )
                .bind(workout_exercise_id)
                .bind(set.set_order)
                .execute(&mut *tx)
                .await?;
            }
        }

        tx.commit().await?;
        Ok(())
    }
}

fn assemble_workouts(rows: &[PgRow]) -> Vec<Workout> {
    let mut workouts_map: HashMap<i32, Workout> = HashMap::new();
    let mut exercises_map: HashMap<(i32, i32), WorkoutExercise> = HashMap::new(); // (workout_id, we_id)
    let mut seen_sets: HashMap<i32, Vec<WorkoutSet>> = HashMap::new(); // we_id -> sets

    for row in rows {
        let workout_id: i32 = row.get("workout_id");

        // Insert workout if new
        workouts_map.entry(workout_id).or_insert_with(|| Workout {
            id: workout_id,
            name: row.get("workout_name"),
            description: row
                .get::<Option<String>, _>("workout_description")
                .unwrap_or_default(),
            user_id: row.get("user_id"),
            exercises: vec![],
        });

        // Process exercise (if present from LEFT JOIN)
        let we_id: Option<i32> = row.get("workout_exercise_id");
        if let Some(we_id) = we_id {
            exercises_map
                .entry((workout_id, we_id))
                .or_insert_with(|| WorkoutExercise {
                    exercise: Exercise {
                        id: row.get("exercise_id"),
                        name: row.get("exercise_name"),
                        description: row
                            .get::<Option<String>, _>("exercise_description")
                            .unwrap_or_default(),
                        is_personal: row.get("is_personal"),
                    },
                    order_index: row.get("exercise_order"),
                    sets: vec![],
                });

            // Process set (if present from LEFT JOIN)
            let set_id: Option<i32> = row.get("set_id");
            if let Some(set_id) = set_id {
                let sets = seen_sets.entry(we_id).or_default();
                if !sets.iter().any(|s| s.id == set_id) {
                    sets.push(WorkoutSet {
                        id: set_id,
                        set_order: row.get("set_order"),
                    });
                }
            }
        }
    }

    // Orders sets by set_order and attaches them to exercises
    for ((_, we_id), exercise) in exercises_map.iter_mut() {
        if let Some(mut sets) = seen_sets.remove(we_id) {
            sets.sort_by_key(|s| s.set_order);
            exercise.sets = sets;
        }
    }

    // Attach exercises to workouts
    for ((workout_id, _), exercise) in exercises_map {
        if let Some(workout) = workouts_map.get_mut(&workout_id) {
            workout.exercises.push(exercise);
        }
    }

    // Sort exercises by order_index
    for workout in workouts_map.values_mut() {
        workout.exercises.sort_by_key(|e| e.order_index);
    }

    let mut workouts: Vec<Workout> = workouts_map.into_values().collect();
    workouts.sort_by_key(|w| w.id);
    workouts
}

fn assemble_exercises(rows: &[ExerciseRow]) -> Vec<Exercise> {
    rows.iter()
        .map(|row| Exercise {
            id: row.id,
            name: row.name.clone(),
            description: row.description.clone().unwrap_or_default(),
            is_personal: row.is_personal,
        })
        .collect()
}
