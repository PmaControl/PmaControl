#!/usr/bin/env bash

set -e

ROOT=${1:-.}

if [ ! -f "$ROOT/.codexignore" ]; then
  echo "No .codexignore found in $ROOT"
  exit 1
fi

echo "Calculating size respecting .codexignore..."

# liste des fichiers respectant .codexignore
FILES=$(rg --files \
  --hidden \
  --ignore-file "$ROOT/.codexignore" \
  --glob '!.git/*' \
  "$ROOT")

TOTAL=0

while IFS= read -r file; do
    if [ -f "$file" ]; then
        SIZE=$(stat -c%s "$file")
        TOTAL=$((TOTAL + SIZE))
    fi
done <<< "$FILES"

echo
echo "Total size (respecting .codexignore):"
numfmt --to=iec $TOTAL
