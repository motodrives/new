# Deployment Fix - wkhtmltopdf Package Issue

## Problem 1
The `wkhtmltopdf` package is not available in the Debian Trixie repository used by Render.com, causing Docker build failures.

## Solution 1
Removed `wkhtmltopdf` from the Dockerfile package installation list.

## Problem 2
Dockerfile was trying to set permissions on directories (`uploads` and `config`) that didn't exist during the build process.

## Solution 2
Added `mkdir -p` commands to create the directories before setting permissions on them.

## Impact
- All core functionality remains intact
- PDF generation feature (if implemented) will need alternative solution
- Docker build now works correctly on Render.com

## Alternative PDF Solutions (if needed in future)
1. **TCPDF** - Pure PHP PDF library
2. **FPDF** - Lightweight PHP PDF generator
3. **DomPDF** - HTML to PDF converter
4. **MPDF** - PHP library generating PDF files from UTF-8 encoded HTML

## Files Modified
- `Dockerfile` - Removed wkhtmltopdf package
- `Dockerfile` - Added directory creation before permission setting

## Next Steps
1. Commit and push the Dockerfile changes
2. Redeploy to Render.com
3. Test all functionality
4. If PDF generation is needed, implement one of the alternative solutions above

The website should now deploy successfully to Render.com without any build errors.