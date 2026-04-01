use sqlx::PgPool;
use crate::models::Workout;
use crate::rows::WorkoutRow;
use anyhow::Result;
use async_trait::async_trait;

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
            "SELECT id, name, user_id FROM workout WHERE id = $1"
        )
        .bind(workout_id)
        .fetch_one(&self.pool)
        .await?;

        // Map database row to domain model
        let workout = Workout {
            id: row.id,
            name: row.name,
            user_id: row.user_id,
            exercises: vec![],  // TODO: Fetch exercises separately
        };

        Ok(workout)
    }
}
