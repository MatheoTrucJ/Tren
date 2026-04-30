//! Conversions - Transform flat DB rows into nested domain models

use crate::models::*;
use crate::rows::*;
use std::collections::HashMap;

// ============================================================================
// Simple 1:1 conversions
// ============================================================================

impl From<UserRow> for User {
    fn from(row: UserRow) -> Self {
        User {
            id: row.id,
            username: row.username,
            birth_year: row.birth_year,
            created_at: row.created_at,
        }
    }
}

impl From<ExerciseRow> for Exercise {
    fn from(row: ExerciseRow) -> Self {
        Exercise {
            id: row.id,
            name: row.name,
            description: row.description.unwrap_or_default(),
            is_personal: row.is_personal,
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
    let exercise_map: HashMap<i32, Exercise> =
        exercises.into_iter().map(|e| (e.id, e.into())).collect();

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
            let sets = sets_by_workout_exercise.remove(&we.id).unwrap_or_default();

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
        description: workout.description.unwrap_or_default(),
        user_id: workout.user_id,
        exercises: workout_exercises_domain,
    }
}

/// Assembles a full WorkoutSession with nested exercise logs from flat rows
pub fn assemble_session(
    session: WorkoutSessionRow,
    session_exercises: Vec<SessionExerciseRow>,
    session_sets: Vec<SessionSetRow>,
    set_logs: Vec<SetLogRow>,
    exercises: Vec<ExerciseRow>,
) -> WorkoutSession {
    // Build exercise lookup map
    let exercise_map: HashMap<i32, Exercise> =
        exercises.into_iter().map(|e| (e.id, e.into())).collect();

    // Group set logs by session_set_id
    let mut logs_by_session_set: HashMap<i32, Vec<SetLog>> = HashMap::new();
    for log in set_logs {
        logs_by_session_set
            .entry(log.session_set_id)
            .or_default()
            .push(log.into());
    }

    // Build session sets grouped by session_exercise_id
    let mut sets_by_session_exercise: HashMap<i32, Vec<SessionSet>> = HashMap::new();
    for session_set in session_sets {
        let logs = logs_by_session_set
            .remove(&session_set.id)
            .unwrap_or_default();

        sets_by_session_exercise
            .entry(session_set.session_exercise_id)
            .or_default()
            .push(SessionSet {
                id: session_set.id,
                set_order: session_set.set_order,
                logs,
            });
    }

    for sets in sets_by_session_exercise.values_mut() {
        sets.sort_by_key(|s| s.set_order);
    }

    let mut logged_exercises: Vec<SessionExerciseLog> = session_exercises
        .into_iter()
        .filter_map(|session_exercise| {
            let exercise = exercise_map.get(&session_exercise.exercise_id)?.clone();
            let sets = sets_by_session_exercise
                .remove(&session_exercise.id)
                .unwrap_or_default();

            Some(SessionExerciseLog {
                id: session_exercise.id,
                exercise,
                exercise_order: session_exercise.exercise_order,
                sets,
            })
        })
        .collect();

    logged_exercises.sort_by_key(|exercise| exercise.exercise_order);

    WorkoutSession {
        id: session.id,
        user_id: session.user_id,
        start_time: session.start_time,
        end_time: session.end_time,
        notes: session.notes,
        logged_exercises,
    }
}
