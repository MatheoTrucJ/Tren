# Copilot Instructions for Tren

## Project Overview

**Tren** is a Rust project (project type indicated by `.gitignore` configuration). The project is in early development stages with a basic Rust project structure under `tren_api/`.

### Architecture

- **Structure**: Monorepo with Rust API in `tren_api/`
- **Build System**: Cargo (Rust's standard package manager)
- **Target**: This is a Rust API project, likely to be a REST API or backend service based on the naming convention

## Build, Test, and Lint Commands

### Project Setup
```bash
# Initialize/update Rust toolchain (if needed)
rustup update

# Build the project
cargo build
cargo build --release  # Optimized build for production
```

### Testing
```bash
# Run all tests
cargo test

# Run tests for a specific module/crate
cargo test --package tren_api

# Run a single test (by name)
cargo test --lib test_name -- --exact

# Run with output (don't capture stdout/stderr)
cargo test -- --nocapture

# Run tests with specific features
cargo test --features feature_name
```

### Linting and Code Quality
```bash
# Check code without building (faster than cargo build)
cargo check

# Run Clippy for linting/suggestions
cargo clippy

# Run Clippy with all warn-level lints
cargo clippy -- -W clippy::all

# Format code
cargo fmt

# Check formatting without changing files
cargo fmt -- --check
```

### Debugging
```bash
# Run with debug output
RUST_BACKTRACE=1 cargo run

# Full backtrace (very verbose)
RUST_BACKTRACE=full cargo run
```

## Key Conventions

### Code Organization

- **Entry Point**: `tren_api/main.rs` - Main application entry point
- **Module Structure**: Use module-first organization; define modules in separate files or directories when complexity grows
- **Error Handling**: Prefer explicit `Result` types with context via libraries like `anyhow` or `thiserror`

### Naming Conventions

- **Files/Modules**: Use `snake_case` (Rust standard)
- **Types/Traits**: Use `PascalCase`
- **Constants**: Use `SCREAMING_SNAKE_CASE`

### Dependencies and Cargo.toml

- Keep dependencies minimal and up-to-date; regularly run `cargo update`
- Common useful crates for APIs:
  - **async runtime**: `tokio`
  - **web framework**: `axum`, `actix-web`, or `rocket`
  - **serialization**: `serde` + `serde_json`
  - **error handling**: `anyhow` or `thiserror`
  - **logging**: `tracing` or `log` + `env_logger`

### Testing Patterns

- Unit tests: Co-locate with code using `#[cfg(test)]` modules
- Integration tests: Place in `tests/` directory at crate root
- Test structure: Follow Arrange-Act-Assert pattern

### Common Patterns for API Development

- Use `async/await` with Tokio for concurrent request handling
- Struct-based configuration for settings
- Implement `serde::Deserialize` for request validation
- Use middleware for cross-cutting concerns (auth, logging, CORS)

## Development Workflow Tips

- **Incremental Development**: Use `cargo check` frequently during development (faster feedback than building)
- **Feature Flags**: Use Cargo features for conditional compilation of optional functionality
- **Environment Variables**: Use `.env` files with `dotenv` crate for local configuration
- **Documentation**: Generate with `cargo doc --open` and include doc comments (`///`) for public APIs

## Rust Edition

This project likely targets Rust 2021 Edition (standard for modern Rust projects). If uncertain, check `Cargo.toml` for the `edition` field.

---

**Note**: As this is an early-stage project, architecture and conventions may evolve. Update this file as the project structure and patterns become established.
