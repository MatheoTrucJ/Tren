use sqlx::PgPool;
use crate::models::Workout;
use anyhow::Result;

/// Trait for workout data access operations
pub trait WorkoutRepository {
    /// Fetch a workout by ID
    fn get_workout_by_id(&self, workout_id: i32) -> impl std::future::Future<Output = Result<Workout>> + Send;
}

/// Concrete implementation of WorkoutRepository
pub struct WorkoutRepositoryImpl {
    pool: PgPool,
}

impl WorkoutRepositoryImpl {
    pub fn new(pool: PgPool) -> Self {
        Self { pool }
    }
}

impl WorkoutRepository for WorkoutRepositoryImpl {
    async fn get_workout_by_id(&self, workout_id: i32) -> Result<Workout> {
        let workout = sqlx::query_as::<_, Workout>(
            "SELECT workout_id, workout_name, user_id_fk FROM workout WHERE workout_id = $1"
        )
        .bind(workout_id)
        .fetch_one(&self.pool)
        .await?;

        Ok(workout)
    }
}
