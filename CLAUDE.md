# Claude Instructions for SmartERP

These instructions are mandatory for all tasks in this project.

## Required Context Files

Before starting ANY task, you must read and apply the contents of:

1. `claude-memory.md`
   - Contains compressed project knowledge (architecture, modules, models, flows, stack)

2. `claude-rules.md`
   - Contains coding standards, patterns, and architectural rules

## Behavior Rules

- Always treat both files as authoritative project context.
- Re-read them whenever a task involves design decisions, architecture, or code changes.
- Do not contradict rules defined in these files.
- If there is a conflict between general assumptions and these files, the files take priority.

## Maintenance

- If new decisions, modules, or patterns emerge during development, update `claude-memory.md`.
- If coding standards evolve, update `claude-rules.md`.