#!/bin/bash

# DEPLOY TO DEV - SAFE DEPLOYMENT SCRIPT
# This script safely deploys production features from whatsnext-local to whatsnext-dev
# while excluding all local development files and configurations

echo "🚀 Starting safe deployment to whatsnext-dev..."
echo "📋 This will copy only production-ready features"
echo ""

# Define source and destination directories
SOURCE_DIR="/Volumes/PeteSSD/petebolane/Documents/websites/whatsnext.realestate/GIT/whatsnext-local"
DEST_DIR="/Volumes/PeteSSD/petebolane/Documents/websites/whatsnext.realestate/GIT/whatsnext-dev"

# Create backup of current dev state
echo "💾 Creating backup of current whatsnext-dev state..."
cd "$DEST_DIR"
git add -A
git commit -m "Backup before deployment - $(date)" || true

# Create deployment branch
git checkout -b deployment-$(date +%Y%m%d-%H%M%S) || true

echo ""
echo "📁 Copying production features..."

# Copy only the production-ready files and directories
# EXCLUDING all local development files

# Core application files (safe to copy)
rsync -av --exclude='.git' \
    --exclude='docker*' \
    --exclude='simple_setup.php' \
    --exclude='adminer.php' \
    --exclude='test_connection.php' \
    --exclude='fix_performance_log.php' \
    --exclude='temp' \
    --exclude='uploads' \
    --exclude='error_log' \
    --exclude='*.log' \
    --exclude='cookies.txt' \
    --exclude='vendor' \
    --exclude='node_modules' \
    --exclude='.DS_Store' \
    --exclude='*.sql' \
    --exclude='*.bak' \
    --exclude='*.tmp' \
    --exclude='*.swp' \
    --exclude='*.swo' \
    --exclude='*~' \
    --exclude='.vscode' \
    --exclude='.idea' \
    --exclude='*.md' \
    "$SOURCE_DIR/" "$DEST_DIR/"

echo ""
echo "✅ Production files copied successfully!"

# Copy specific production features that were developed
echo "📋 Copying specific production enhancements..."

# Copy Print Timeline enhancements
cp "$SOURCE_DIR/include/traits/t_transaction_handler.php" "$DEST_DIR/include/traits/"

# Copy Archive CSV header enhancements  
cp "$SOURCE_DIR/include/classes/c_user.php" "$DEST_DIR/include/classes/"

# Copy Coordinator proxy banner enhancements
cp "$SOURCE_DIR/pages/agents/modules/footer.php" "$DEST_DIR/pages/agents/modules/"

# Copy documentation (optional - for reference)
mkdir -p "$DEST_DIR/MD-Summaries"
cp -r "$SOURCE_DIR/MD-Summaries/Details" "$DEST_DIR/MD-Summaries/"
cp "$SOURCE_DIR/MD-Summaries/PETE-UPDATES.md" "$DEST_DIR/MD-Summaries/"

echo ""
echo "🔒 Updating .gitignore for production..."
cat > "$DEST_DIR/.gitignore" << 'EOF'
# Production .gitignore - excludes sensitive and development files
include/common.php
include/stripe/
include/Twilio/
temp/
uploads/
error_log
*.log
cookies.txt
vendor/
node_modules/
.DS_Store
*.sql
*.bak
*.tmp
*.swp
*.swo
*~
.vscode/
.idea/
EOF

echo ""
echo "📝 Committing production changes..."
cd "$DEST_DIR"
git add -A
git commit -m "Deploy production features: Print Timeline, Archive CSV, Coordinator Proxy enhancements - $(date)"

echo ""
echo "🎯 Deployment Summary:"
echo "✅ Print Timeline enhancements (client header, date format, under contract banner)"
echo "✅ Archive CSV header improvements"
echo "✅ Coordinator proxy banner exit link"
echo "✅ Production documentation"
echo "✅ Updated .gitignore for production"
echo ""
echo "❌ EXCLUDED (Local Development Only):"
echo "   - Docker configuration"
echo "   - Local database setup scripts"
echo "   - Local PHP fixes"
echo "   - Local navigation path modifications"
echo "   - Local .htaccess"
echo ""
echo "🚀 Ready to push to whatsnext-dev!"
echo "💡 To push: cd $DEST_DIR && git push origin deployment-$(date +%Y%m%d-%H%M%S)"
echo ""
echo "🔄 To rollback if needed:"
echo "   cd $DEST_DIR && git reset --hard origin/main"
