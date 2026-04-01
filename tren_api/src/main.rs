mod controllers;
mod services;
mod repositories;
mod models;
mod rows;

use repositories::PostgresWorkoutRepository;
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
                let workout_repo = PostgresWorkoutRepository::new(pool);
                println!("Database connected successfully");
                println!("PostgreSQL Repository ready for injection: {:?}", &workout_repo);
            }
            Err(e) => {
                eprintln!("Failed to connect to database: {}", e);
            }
        }
    } else {
        println!("DATABASE_URL not set, skipping database connection");
    }
}
