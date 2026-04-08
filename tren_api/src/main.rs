mod controllers;
mod conversions;
mod models;
mod repositories;
mod rows;
mod services;

use std::sync::Arc;

use models::{Exercise, Workout, WorkoutExercise, WorkoutSet};
use repositories::PostgresWorkoutRepository;
use services::{DefaultWorkoutService, WorkoutService};
use sqlx::postgres::PgPoolOptions;

#[tokio::main]
async fn main() {
    // Load environment variables from .env file
    dotenv::dotenv().ok();

    println!("Tren API - Starting...");

    // Database connection example (requires DATABASE_URL env var)
    let database_url = std::env::var("DATABASE_URL").unwrap_or_default();

    if !database_url.is_empty() {
        match PgPoolOptions::new()
            .max_connections(5)
            .connect(&database_url)
            .await
        {
            Ok(pool) => {
                // Dependency injection: create repository with pool
                let workout_repo = Arc::new(PostgresWorkoutRepository::new(pool));
                let workout_service = DefaultWorkoutService::new(workout_repo.clone());
                println!("Database connected successfully");
                println!(
                    "PostgreSQL Repository ready for injection: {:?}",
                    &workout_repo
                );
                println!("Workout service ready for injection");

                let sample_workout = Workout {
                    id: 0,
                    name: "Push/Pull Smoke Test".to_string(),
                    description: "Inserted from main.rs to verify create_workout".to_string(),
                    user_id: 1,
                    exercises: vec![
                        WorkoutExercise {
                            exercise: Exercise {
                                id: 1,
                                name: "Squat".to_string(),
                                description: "Barbell squat".to_string(),
                            },
                            order_index: 1,
                            sets: vec![
                                WorkoutSet {
                                    id: 0,
                                    set_order: 1,
                                },
                                WorkoutSet {
                                    id: 0,
                                    set_order: 2,
                                },
                                WorkoutSet {
                                    id: 0,
                                    set_order: 3,
                                },
                            ],
                        },
                        WorkoutExercise {
                            exercise: Exercise {
                                id: 2,
                                name: "Bench Press".to_string(),
                                description: "Barbell bench press".to_string(),
                            },
                            order_index: 2,
                            sets: vec![
                                WorkoutSet {
                                    id: 0,
                                    set_order: 1,
                                },
                                WorkoutSet {
                                    id: 0,
                                    set_order: 2,
                                },
                                WorkoutSet {
                                    id: 0,
                                    set_order: 3,
                                },
                            ],
                        },
                    ],
                };

                match workout_service.create_workout(&sample_workout).await {
                    Ok(()) => println!("Inserted sample workout for user_id=1"),
                    Err(e) => eprintln!("Failed to insert sample workout: {}", e),
                }
            }
            Err(e) => {
                eprintln!("Failed to connect to database: {}", e);
            }
        }
    } else {
        println!("DATABASE_URL not set, skipping database connection");
    }
}
