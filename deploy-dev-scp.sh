#!/bin/bash

REMOTE_USER="pbolane1"
REMOTE_HOST="dev.whatsnext.realestate"
REMOTE_PATH="/home2/pbolane1/public_html/dev.whatsnext.realestate"
LOCAL_BASE="/Volumes/ExternalSSD/petebolane/Documents/websites/whatsnext.realestate/app.whatsnext.realestate/snapshot 7-14-25"

# List of files to upload
FILES=(
  "pages/agents/js/wysiwyg_upload.php"
)

for FILE in "${FILES[@]}"; do
  echo "Uploading $FILE..."
  scp "$LOCAL_BASE/$FILE" "$REMOTE_USER@$REMOTE_HOST:$REMOTE_PATH/$FILE"
done

echo "✅ Done uploading selected files via SCP."
