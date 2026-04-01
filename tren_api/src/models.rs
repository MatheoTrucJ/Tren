use chrono::{DateTime, Utc};
use sqlx::FromRow;

#[derive(Debug, Clone, FromRow)]
pub struct User {
    pub user_id: i32,
    pub username: String,
    pub password: String,
    pub birth_year: i32,
    pub created_at: DateTime<Utc>,
}

#[derive(Debug, Clone, FromRow)]
pub struct Exercise {
    pub exercise_id: i32,
    pub exercise_name: String,
    pub description: String,
}

#[derive(Debug, Clone, FromRow)]
pub struct GeneralExercise {
    pub exercise_id_fk: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct UserExercise {
    pub exercise_id_fk: i32,
    pub user_id_fk: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct Workout {
    pub workout_id: i32,
    pub workout_name: String,
    pub user_id_fk: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct WorkoutExercise {
    pub workout_exercise_id: i32,
    pub exercise_id_fk: i32,
    pub workout_id_fk: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct WorkoutSet {
    pub workout_set_id: i32,
    pub set_number: i32,
    pub workout_exercise_id_fk: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct WorkoutSession {
    pub session_id: i32,
    pub start_time: DateTime<Utc>,
    pub end_time: DateTime<Utc>,
    pub notes: String,
    pub workout_id_fk: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct SetLog {
    pub set_log_id: i32,
    pub weight: f64,
    pub reps: i32,
    pub set_note: String,
    pub workout_set_id_fk: i32,
    pub session_id_fk: i32,
}
