use sqlx::{PgPool, FromRow};
use crate::models::Workout;
use anyhow::Result;
use async_trait::async_trait;

/// Database row model for workout (maps directly to SQL)
#[derive(Debug, Clone, FromRow)]
struct WorkoutRow {
    workout_id: i32,
    workout_name: String,
    user_id_fk: i32,
}

/// Trait for workout data access operations
#[async_trait]
pub trait WorkoutRepository {
    /// Fetch a workout by ID
    async fn get_workout_by_id(&self, workout_id: i32) -> Result<Workout>;
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
        let row = sqlx::query_as::<_, WorkoutRow>(
            "SELECT workout_id, workout_name, user_id_fk FROM workout WHERE workout_id = $1"
        )
        .bind(workout_id)
        .fetch_one(&self.pool)
        .await?;

        // Map database row to domain model
        let workout = Workout {
            id: row.workout_id,
            name: row.workout_name,
            user_id: row.user_id_fk,
            exercises: vec![],  // Exercises would be fetched separately
        };

        Ok(workout)
    }
}
