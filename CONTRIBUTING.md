# Contributing to Laravel AI Image Generator

Thank you for considering contributing to the Laravel AI Image Generator package! This document outlines the guidelines for contributing.

## Development Setup

1. Fork the repository
2. Clone your fork: `git clone https://github.com/openbackend/laravel-ai-image-generator`
3. Install dependencies: `composer install`
4. Copy `.env.example` to `.env` and configure your test API keys
5. Run tests: `composer test`

## How to Contribute

### Reporting Issues

- Use the issue tracker on GitHub
- Provide clear description of the issue
- Include steps to reproduce
- Add relevant environment information

### Pull Requests

1. Create a feature branch: `git checkout -b feature/new-feature`
2. Make your changes
3. Add tests for new functionality
4. Ensure all tests pass: `composer test`
5. Run static analysis: `composer analyse`
6. Commit with clear messages
7. Push to your fork
8. Create a pull request

### Coding Standards

- Follow PSR-12 coding standards
- Use meaningful variable and method names
- Add PHPDoc comments for public methods
- Write tests for new features
- Keep backward compatibility when possible

### Testing

- All new features must include tests
- Maintain test coverage above 80%
- Use meaningful test descriptions
- Mock external API calls in tests

### Documentation

- Update README.md for new features
- Add examples for new functionality
- Update CHANGELOG.md
- Include PHPDoc comments

## Adding New Providers

To add a new AI provider:

1. Create a provider class implementing `AIImageProviderInterface`
2. Add configuration options to the config file
3. Create comprehensive tests
4. Update documentation
5. Add example usage

## Code Review Process

1. All PRs require review before merging
2. Address feedback promptly
3. Keep PRs focused on single features
4. Ensure CI passes

## Release Process

1. Update CHANGELOG.md
2. Update version numbers
3. Create release notes
4. Tag the release

Thank you for your contributions!
