//! Conversions - Transform flat DB rows into nested domain models

use std::collections::HashMap;
use crate::models::*;
use crate::rows::*;

// ============================================================================
// Simple 1:1 conversions
// ============================================================================

impl From<UserRow> for User {
    fn from(row: UserRow) -> Self {
        User {
            id: row.id,
            username: row.username,
            birth_year: row.birth_year,
            created_at: row.created_at.unwrap_or_default(),
        }
    }
}

impl From<ExerciseRow> for Exercise {
    fn from(row: ExerciseRow) -> Self {
        Exercise {
            id: row.id,
            name: row.name,
            description: row.description.unwrap_or_default(),
        }
    }
}

impl From<WorkoutSetRow> for WorkoutSet {
    fn from(row: WorkoutSetRow) -> Self {
        WorkoutSet {
            id: row.id,
            set_order: row.set_order,
        }
    }
}

impl From<SetLogRow> for SetLog {
    fn from(row: SetLogRow) -> Self {
        SetLog {
            id: row.id,
            weight: row.weight,
            reps: row.reps,
            note: row.note,
            template_set_id: row.workout_set_id,
        }
    }
}

// ============================================================================
// Complex nested conversions
// ============================================================================

/// Assembles a full Workout with nested exercises and sets from flat rows
pub fn assemble_workout(
    workout: WorkoutRow,
    workout_exercises: Vec<WorkoutExerciseRow>,
    exercises: Vec<ExerciseRow>,
    sets: Vec<WorkoutSetRow>,
) -> Workout {
    // Build exercise lookup map
    let exercise_map: HashMap<i32, Exercise> = exercises
        .into_iter()
        .map(|e| (e.id, e.into()))
        .collect();

    // Group sets by workout_exercise_id
    let mut sets_by_workout_exercise: HashMap<i32, Vec<WorkoutSet>> = HashMap::new();
    for set in sets {
        sets_by_workout_exercise
            .entry(set.workout_exercise_id)
            .or_default()
            .push(set.into());
    }

    // Sort sets within each group
    for sets in sets_by_workout_exercise.values_mut() {
        sets.sort_by_key(|s| s.set_order);
    }

    // Build workout exercises
    let mut workout_exercises_domain: Vec<WorkoutExercise> = workout_exercises
        .into_iter()
        .filter_map(|we| {
            let exercise = exercise_map.get(&we.exercise_id)?.clone();
            let sets = sets_by_workout_exercise
                .remove(&we.id)
                .unwrap_or_default();

            Some(WorkoutExercise {
                exercise,
                order_index: we.exercise_order,
                sets,
            })
        })
        .collect();

    // Sort by order_index
    workout_exercises_domain.sort_by_key(|we| we.order_index);

    Workout {
        id: workout.id,
        name: workout.name,
        user_id: workout.user_id,
        exercises: workout_exercises_domain,
    }
}

/// Assembles a full WorkoutSession with nested exercise logs from flat rows
pub fn assemble_session(
    session: WorkoutSessionRow,
    set_logs: Vec<SetLogRow>,
    exercises: Vec<ExerciseRow>,
) -> WorkoutSession {
    // Build exercise lookup map
    let exercise_map: HashMap<i32, Exercise> = exercises
        .into_iter()
        .map(|e| (e.id, e.into()))
        .collect();

    // Group set logs by exercise_id
    let mut logs_by_exercise: HashMap<i32, Vec<SetLog>> = HashMap::new();
    for log in set_logs {
        let exercise_id = log.exercise_id;
        logs_by_exercise
            .entry(exercise_id)
            .or_default()
            .push(log.into());
    }

    // Build session exercise logs
    let logged_exercises: Vec<SessionExerciseLog> = logs_by_exercise
        .into_iter()
        .filter_map(|(exercise_id, sets)| {
            let exercise = exercise_map.get(&exercise_id)?.clone();
            Some(SessionExerciseLog { exercise, sets })
        })
        .collect();

    WorkoutSession {
        id: session.id,
        start_time: session.start_time,
        end_time: session.end_time,
        notes: session.notes,
        logged_exercises,
    }
}
