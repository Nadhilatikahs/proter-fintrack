#!/bin/bash
# FinTrack - Build and Deploy Assets Script
# This script builds assets locally and prepares them for deployment

echo "🔨 Building production assets..."
npm run build

echo "✅ Build complete! Assets are in public/build/"
echo ""
echo "📦 Next steps:"
echo "1. Commit the built assets:"
echo "   git add public/build"
echo "   git commit -m 'Add built assets for production'"
echo "   git push origin main"
echo ""
echo "2. On server, pull the changes:"
echo "   cd ~/Repositories/proter-fintrack"
echo "   git pull origin main"
echo "   rsync -av --exclude='.git' ~/Repositories/proter-fintrack/ ~/fintrack.poyekterapan.com/"
echo ""
echo "✅ Assets will be deployed without needing npm on server!"

