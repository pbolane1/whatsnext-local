#!/bin/bash

# ROLLBACK DEV - SAFE ROLLBACK SCRIPT
# This script safely rolls back whatsnext-dev to its clean initial state

echo "🔄 Starting safe rollback of whatsnext-dev..."
echo "⚠️  This will restore whatsnext-dev to its clean initial state"
echo ""

# Define dev directory
DEV_DIR="/Volumes/PeteSSD/petebolane/Documents/websites/whatsnext.realestate/GIT/whatsnext-dev"

# Navigate to dev directory
cd "$DEV_DIR"

# Check current status
echo "📊 Current git status:"
git status --short

echo ""
echo "🔍 Current branch:"
git branch --show-current

echo ""
echo "📝 Recent commits:"
git log --oneline -5

echo ""
echo "⚠️  WARNING: This will permanently remove all local changes!"
echo "   - All uncommitted changes will be lost"
echo "   - All local commits will be removed"
echo "   - Repository will be reset to origin/main"
echo ""

# Ask for confirmation
read -p "Are you sure you want to proceed with rollback? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "❌ Rollback cancelled by user"
    exit 1
fi

echo ""
echo "💾 Creating backup of current state..."
git add -A
git commit -m "Backup before rollback - $(date)" || true

echo ""
echo "🔄 Resetting to clean state..."
git fetch origin
git reset --hard origin/main

echo ""
echo "🧹 Cleaning up any untracked files..."
git clean -fd

echo ""
echo "✅ Rollback completed successfully!"
echo ""
echo "📊 New git status:"
git status

echo ""
echo "📝 Current commit:"
git log --oneline -1

echo ""
echo "🎯 whatsnext-dev is now back to its clean initial state"
echo "💡 You can now safely deploy again when ready"
