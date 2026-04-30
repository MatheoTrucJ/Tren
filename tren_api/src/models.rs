use chrono::{DateTime, Utc};
use serde::{Deserialize, Serialize};

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct User {
    pub id: i32,
    pub username: String,
    pub birth_year: Option<i32>,
    pub created_at: DateTime<Utc>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Exercise {
    pub id: i32,
    pub name: String,
    pub description: String,
    pub is_personal: bool,
}

// --- TEMPLATES ---

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct Workout {
    pub id: i32,
    pub name: String,
    pub description: String,
    pub user_id: i32,
    pub exercises: Vec<WorkoutExercise>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct WorkoutExercise {
    pub exercise: Exercise,
    pub order_index: i32,
    pub sets: Vec<WorkoutSet>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct WorkoutSet {
    pub id: i32,
    pub set_order: i32,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct WorkoutSession {
    pub id: i32,
    pub user_id: i32,
    pub workout_id: Option<i32>,
    pub start_time: DateTime<Utc>,
    pub end_time: Option<DateTime<Utc>>,
    pub notes: Option<String>,
    pub logged_exercises: Vec<SessionExerciseLog>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct SessionExerciseLog {
    pub exercise: Exercise,
    pub sets: Vec<SetLog>,
}

#[derive(Debug, Clone, Serialize, Deserialize)]
pub struct SetLog {
    pub id: i32,
    pub weight: Option<f64>,
    pub reps: i32,
    pub note: Option<String>,
    // Optional: link back to the template set if they followed it
    pub workout_set_id: Option<i32>,
}
