mod controllers;
mod conversions;
mod models;
mod repositories;
mod rows;
mod services;

use axum::{routing::get, Json, Router};
use repositories::PostgresWorkoutRepository;
use serde_json::{json, Value};
use services::{DefaultWorkoutService, WorkoutService};
use sqlx::postgres::PgPoolOptions;
use std::{net::SocketAddr, sync::Arc};

//Fix problemet mellem sql script og models af til at kunne insert en workout_session. Order nr for exerciseLog mangler osv osv.
//problemet lige nu er, at de en workout_session skal tages som en template af en workout, men ikke nødvændigivs have direkte reference. 
//Det skal laves om, men burde kræve minimal refactoring i resten af koden bortset fra models, sql scriptet og insert_workout_session, og conversions ofc.

#[tokio::main]
async fn main() {
    dotenv::dotenv().ok();

    let database_url =
        std::env::var("DATABASE_URL").expect("DATABASE_URL must be set to start the API");
    let pool = PgPoolOptions::new()
        .max_connections(5)
        .connect(&database_url)
        .await
        .expect("failed to connect to PostgreSQL");

    let workout_repository = Arc::new(PostgresWorkoutRepository::new(pool));
    let workout_service: Arc<dyn WorkoutService + Send + Sync> =
        Arc::new(DefaultWorkoutService::new(workout_repository));

    let app = Router::new()
        .route("/", get(root))
        .route("/health", get(health))
        .merge(controllers::workouts_controller::router(workout_service));

    let port = std::env::var("PORT")
        .ok()
        .and_then(|value| value.parse::<u16>().ok())
        .unwrap_or(3000);
    let address = SocketAddr::from(([127, 0, 0, 1], port));

    println!("Tren API listening on http://{}", address);

    let listener = tokio::net::TcpListener::bind(address)
        .await
        .expect("failed to bind TCP listener");

    axum::serve(listener, app).await.expect("server failed");
}

async fn root() -> &'static str {
    "Tren API is running"
}

async fn health() -> Json<Value> {
    Json(json!({ "status": "ok" }))
}
