//! Row models - Database row structs that map directly to SQL tables
//! These are used with SQLx's FromRow derive for type-safe queries.

use chrono::{DateTime, Utc};
use sqlx::FromRow;

#[derive(Debug, Clone, FromRow)]
pub struct UserRow {
    pub id: i32,
    pub username: String,
    pub password_hash: String,
    pub birth_year: Option<i32>,
    pub created_at: DateTime<Utc>,
}

#[derive(Debug, Clone, FromRow)]
pub struct ExerciseRow {
    pub id: i32,
    pub name: String,
    pub description: Option<String>,
    pub is_personal: bool,
}

#[derive(Debug, Clone, FromRow)]
pub struct GeneralExerciseRow {
    pub exercise_id: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct UserExerciseRow {
    pub exercise_id: i32,
    pub user_id: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct WorkoutRow {
    pub id: i32,
    pub name: String,
    pub description: Option<String>,
    pub user_id: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct WorkoutExerciseRow {
    pub id: i32,
    pub workout_id: i32,
    pub exercise_id: i32,
    pub exercise_order: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct WorkoutSetRow {
    pub id: i32,
    pub workout_exercise_id: i32,
    pub set_order: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct WorkoutSessionRow {
    pub id: i32,
    pub user_id: i32,
    pub start_time: DateTime<Utc>,
    pub end_time: Option<DateTime<Utc>>,
    pub notes: Option<String>,
}

#[derive(Debug, Clone, FromRow)]
pub struct SessionExerciseRow {
    pub id: i32,
    pub session_id: i32,
    pub exercise_id: i32,
    pub exercise_order: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct SessionSetRow {
    pub id: i32,
    pub session_exercise_id: i32,
    pub set_order: i32,
}

#[derive(Debug, Clone, FromRow)]
pub struct SetLogRow {
    pub id: i32,
    pub exercise_id: i32,
    pub session_set_id: i32,
    pub weight: Option<f64>,
    pub reps: i32,
    pub note: Option<String>,
}
