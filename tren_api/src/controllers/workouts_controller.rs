use rand::Rng;
use std::sync::Arc;

use axum::{
    extract::{Path, State},
    http::StatusCode,
    routing::{get, post},
    Json, Router,
};
use serde_json::{json, Value};

use crate::models::{Exercise, Workout, WorkoutSession};
use crate::services::WorkoutService;

type WorkoutServiceState = Arc<dyn WorkoutService + Send + Sync>;

pub fn router(workout_service: WorkoutServiceState) -> Router {
    Router::new()
        .route("/workouts/user/:id", get(get_user_workouts))
        .route("/workouts/health", get(workouts_health))
        .route("/workouts/ping", get(workouts_ping))
        .route("/workouts", post(create_workout))
        .route(
            "/workouts/exercises/user/:id",
            get(get_all_exercises_for_user),
        )
        .route("/workouts/goon", get(goon))
        .route("/workouts/user/:id", post(finish_workout))
        .with_state(workout_service)
}

async fn get_user_workouts(
    State(workout_service): State<WorkoutServiceState>,
    Path(id): Path<i32>,
) -> Result<Json<Vec<Workout>>, (StatusCode, Json<Value>)> {
    workout_service
        .get_all_workouts_for_user(id)
        .await
        .map(Json)
        .map_err(|error| {
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(json!({
                    "error": "failed to fetch workouts",
                    "details": error.to_string()
                })),
            )
        })
}

async fn create_workout(
    State(workout_service): State<WorkoutServiceState>,
    Json(workout): Json<Workout>,
) -> Result<StatusCode, (StatusCode, Json<Value>)> {
    workout_service
        .create_workout(&workout)
        .await
        .map(|_| StatusCode::CREATED)
        .map_err(|error| {
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(json!({
                    "error": "failed to create workout",
                    "details": error.to_string()
                })),
            )
        })
}

async fn get_all_exercises_for_user(
    State(workout_service): State<WorkoutServiceState>,
    Path(id): Path<i32>,
) -> Result<Json<Vec<Exercise>>, (StatusCode, Json<Value>)> {
    workout_service
        .get_all_exercises_for_user(id)
        .await
        .map(Json)
        .map_err(|error| {
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(json!({
                    "error": "failed to fetch exercises",
                    "details": error.to_string()
                })),
            )
        })
}

async fn finish_workout(
    State(workout_service): State<WorkoutServiceState>,
    Path(user_id): Path<i32>,
    Json(workout_session): Json<WorkoutSession>,
) -> Result<StatusCode, (StatusCode, Json<Value>)> {
    workout_service
        .insert_workout_session(user_id, &workout_session)
        .await
        .map(|_| StatusCode::CREATED)
        .map_err(|error| {
            (
                StatusCode::INTERNAL_SERVER_ERROR,
                Json(json!({
                    "error": "failed to insert workout session",
                    "details": error.to_string()
                })),
            )
        })
}

async fn workouts_health() -> Json<Value> {
    Json(json!({ "status": "ok" }))
}

async fn workouts_ping() -> Json<Value> {
    Json(json!({ "message": "pong" }))
}

async fn goon() -> Json<i32> {
    let random_number = rand::thread_rng().gen_range(0..31);
    Json(random_number)
}
