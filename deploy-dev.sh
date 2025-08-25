#!/bin/bash

HOST="dev.whatsnext.realestate"
USER="pbolane1"
LOCAL_DIR="/Volumes/ExternalSSD/petebolane/Documents/websites/whatsnext.realestate/app.whatsnext.realestate/snapshot 7-14-25"
REMOTE_DIR="public_html/dev.whatsnext.realestate"

echo
echo "📁 Scanning for files modified today..."
MODIFIED_FILES=$(find "$LOCAL_DIR" -type f -mtime -1)

if [ -z "$MODIFIED_FILES" ]; then
  echo "✅ No files modified today. Nothing to upload."
  exit 0
fi

BATCH_FILE=$(mktemp)

while IFS= read -r file; do
  REL_PATH="${file#$LOCAL_DIR/}"
  DIR_PATH=$(dirname "$REMOTE_DIR/$REL_PATH")
  echo "mkdir \"$DIR_PATH\"" >> "$BATCH_FILE"
  echo "put \"$file\" \"$REMOTE_DIR/$REL_PATH\"" >> "$BATCH_FILE"
done <<< "$MODIFIED_FILES"

echo
echo "📤 Uploading modified files to dev server..."

# Manual password SFTP session
sftp "$USER@$HOST" <<EOF
$(cat "$BATCH_FILE")
EOF

rm "$BATCH_FILE"
echo
