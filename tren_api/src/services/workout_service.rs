use std::sync::Arc;

use anyhow::Result;
use async_trait::async_trait;

use crate::models::*;
use crate::repositories::WorkoutRepository;

#[async_trait]
pub trait WorkoutService: Send + Sync {
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

pub struct DefaultWorkoutService {
    workout_repository: Arc<dyn WorkoutRepository + Send + Sync>,
}

impl DefaultWorkoutService {
    pub fn new(workout_repository: Arc<dyn WorkoutRepository + Send + Sync>) -> Self {
        Self { workout_repository }
    }
}

#[async_trait]
impl WorkoutService for DefaultWorkoutService {
    async fn get_workout_by_id(&self, workout_id: i32) -> Result<Workout> {
        self.workout_repository.get_workout_by_id(workout_id).await
    }

    async fn insert_workout_session(
        &self,
        user_id: i32,
        workout_session: &WorkoutSession,
    ) -> Result<()> {
        self.workout_repository
            .insert_workout_session(user_id, workout_session)
            .await
    }

    async fn get_all_workouts_for_user(&self, user_id: i32) -> Result<Vec<Workout>> {
        self.workout_repository
            .get_all_workouts_for_user(user_id)
            .await
    }

    async fn get_all_exercises_for_user(&self, id: i32) -> Result<Vec<Exercise>> {
        self.workout_repository.get_all_exercises_for_user(id).await
    }

    async fn get_exercise_by_id(&self, exercise_id: i32) -> Result<Exercise> {
        self.workout_repository
            .get_exercise_by_id(exercise_id)
            .await
    }

    async fn get_workout_sets(&self, workout_exercise_id: i32) -> Result<Vec<WorkoutSet>> {
        self.workout_repository
            .get_workout_sets(workout_exercise_id)
            .await
    }

    async fn create_workout(&self, workout: &Workout) -> Result<()> {
        self.workout_repository.create_workout(workout).await
    }
}
