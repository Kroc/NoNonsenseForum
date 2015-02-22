REM This is a Windows Batch file, the lowest-common denominator for getting things done that the UI never got around to
REM solving. To get this to work fully you need to:

REM *	install imagemagick + ghostscript
REM *	create a "bin" folder and download the following in to it:
REM 	*	pngout.exe
REM 	*	optipng.exe
REM	*	pngcrush.exe

REM P.S. This script will use a _ton_ of RAM, be warned

@ECHO OFF
CLS & COLOR 1F
TITLE Generating images...

REM Change to the current directory
CD /d %~dp0

SET "IMAGEMAGICK=bin\ImageMagick\convert.exe"
If NOT EXIST %IMAGEMAGICK% ECHO Please download ImageMagick to "bin\ImageMagick" & PAUSE & EXIT
SET "MAGICK_CONFIGURE_PATH=%~dp0"

If NOT EXIST "bin\Ghostscript\bin\gswin32c.exe" (
        ECHO Please donwload & install Ghostscript Portable to "bin\Ghostscript"
        PAUSE & EXIT
)

SET "PNGOUT=bin\pngout.exe"
IF NOT EXIST %PNGOUT% ECHO Please download PNGOut to "%PNGOUT%" & PAUSE & EXIT

SET "OPTIPNG=bin\optipng.exe"
IF NOT EXIST %OPTIPNG% ECHO Please download OptiPNG to "%OPTIPNG%" & PAUSE & EXIT

SET "PNGCRUSH=bin\pngcrush.exe"
IF NOT EXIST %PNGCRUSH% ECHO Please download PNGCrush to "%PNGCRUSH%" & PAUSE & EXIT

:: generate spritesheets to work from
:: =========================================================================================================================
:: white 1x
:: -------------------------------------------------------------------------------------------------------------------------
:: convert the vector EPS to PNG, masking the alpha.
:: the antialias applied by default is no good, so we do it manually by resizing a very hires image down
IF NOT EXIST icons_white_1x.png (
	ECHO icons white 1x...
	%IMAGEMAGICK%	+antialias -density 648 refs\brightmixIconset_v02.eps -colorspace RGB ^
			-background "#f4f3f5" -resize 998x702 -alpha Shape -channel a -negate ^
			icons_white_1x.png
)

:: white 2x
:: -------------------------------------------------------------------------------------------------------------------------
:: produce a version double the size for hi-DPI screens
IF NOT EXIST icons_white_2x.png (
	ECHO icons white 2x...
	%IMAGEMAGICK%   +antialias -density 648 refs\brightmixIconset_v02.eps -colorspace RGB ^
                        -background "#f4f3f5" -resize 1996x1404 -alpha Shape -channel a -negate ^
                        icons_white_2x.png
)
:: black half size
:: -------------------------------------------------------------------------------------------------------------------------
:: the append icon is only available in large size (default:32x32), create a ½-size image so that we can get a 16x16 icon
IF NOT EXIST icons_black_half.png (
	ECHO icons_black_half.png
	%IMAGEMAGICK%   +antialias -density 144 refs\brightmixIconset_v02.eps -colorspace RGB ^
                        -background "#f4f3f5" -resize 499x351 -alpha Shape -channel a -negate ^
                        icons_black_half.png
	%IMAGEMAGICK%   icons_black_half.png -negate icons_black_half.png
)
:: black 1x
:: -------------------------------------------------------------------------------------------------------------------------
:: invert to produce the black set
IF NOT EXIST icons_black_1x.png (
	ECHO icons black 1x...
	%IMAGEMAGICK%   icons_white_1x.png -negate icons_black_1x.png
)
:: black 2x
:: -------------------------------------------------------------------------------------------------------------------------
:: invert to produce the black 2x set
IF NOT EXIST icons_black_2x.png (
	ECHO icons black 2x...
	%IMAGEMAGICK%   icons_white_2x.png -negate icons_black_2x.png
)
ECHO.

:: crop and save the individual icons needed
:: -------------------------------------------------------------------------------------------------------------------------
:: some optimisations we apply:
:: 	`-background "#eeeeee"`		IE6 doesn’t support transparency so we add a useful background colour to fallback on
::	`-depth 8`			convert source image to 8-bit depth (255-colours)
::	`-define png:depth=8`		and output as 8-bit too
::	`-define png:compression...`	don’t compress, we’re going to optimise all the PNGs compression afterwards

ECHO * add.png
%IMAGEMAGICK%   icons_black_1x.png -crop "16x16+875+152" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                add.png
ECHO * add_2x.png
%IMAGEMAGICK%   icons_black_2x.png -crop "32x32+1750+304" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                add_2x.png

ECHO * append.png
%IMAGEMAGICK%   icons_black_half.png -crop "16x16+137+27" ^
                -background "#888888" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                append.png
ECHO * append_2x.png
%IMAGEMAGICK%   icons_black_1x.png -crop "32x32+273+54" ^
                -background "#888888" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                append_2x.png

ECHO * delete.png
%IMAGEMAGICK%   icons_black_half.png -crop "16x16+312+135" ^
                -background "#888888" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                delete.png
ECHO * delete_2x.png
%IMAGEMAGICK%   icons_black_1x.png -crop "32x32+625+270" ^
                -background "#888888" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                delete_2x.png

ECHO * deletetitle.png
%IMAGEMAGICK%   icons_white_1x.png -crop "32x32+625+270" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                deletetitle.png
ECHO * deletetitle_2x.png
%IMAGEMAGICK%   icons_white_2x.png -crop "64x64+1250+540" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                deletetitle_2x.png

ECHO * folders.png
%IMAGEMAGICK%   icons_white_1x.png -crop "32x32+478+54" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                folders.png
ECHO * folders_2x.png
%IMAGEMAGICK%   icons_white_2x.png -crop "64x64+956+108" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                folders_2x.png

ECHO * go.png (2x)
%IMAGEMAGICK%   icons_black_2x.png -crop "40x40+1352+228" ^
                -background "#ffffff" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                go.png

ECHO * help.png
%IMAGEMAGICK%   icons_white_1x.png -crop "32x32+273+432" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                help.png
ECHO * help_2x.png
%IMAGEMAGICK%   icons_white_2x.png -crop "64x64+546+864" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                help_2x.png

ECHO * here.png
%IMAGEMAGICK%   icons_black_1x.png -crop "16x20+979+58" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                here.png
ECHO * here_2x.png
%IMAGEMAGICK%   icons_black_2x.png -crop "32x40+1958+116" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                here_2x.png

ECHO * lang.png (2x)
%IMAGEMAGICK%   icons_black_2x.png -crop "40x40+1956+234" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                lang.png

ECHO * logo.png (2x)
%IMAGEMAGICK%   icons_white_2x.png -crop "64x64+956+0" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                logo.png

ECHO * new.png
%IMAGEMAGICK%   icons_white_1x.png -crop "32x32+273+54" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                new.png
ECHO * new_2x.png
%IMAGEMAGICK%   icons_white_2x.png -crop "64x64+546+108" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                new_2x.png

ECHO * replies.png
%IMAGEMAGICK%   icons_white_1x.png -crop "32x32+273+487" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                replies.png
ECHO * replies_2x.png
%IMAGEMAGICK%   icons_white_2x.png -crop "64x64+546+974" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                replies_2x.png

ECHO * rss.png
%IMAGEMAGICK%   icons_black_1x.png -crop "16x16+182+62" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                rss.png
ECHO * rss_2x.png
%IMAGEMAGICK%   icons_black_2x.png -crop "32x32+364+124" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                rss_2x.png

ECHO * search.png
%IMAGEMAGICK%   icons_black_1x.png -crop "13x17+536+224" ^
                -background "#ffffff" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                search.png
ECHO * search_2x.png
%IMAGEMAGICK%   icons_black_2x.png -crop "26x34+1072+448" ^
                -background "#ffffff" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                search_2x.png

ECHO * sticky.png
%IMAGEMAGICK%   icons_black_1x.png -crop "16x16+182+223" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                sticky.png
ECHO * sticky_2x.png
%IMAGEMAGICK%   icons_black_2x.png -crop "32x32+362+446" ^
                -background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                sticky_2x.png

ECHO * submit.png (2x)
%IMAGEMAGICK%   icons_white_2x.png -crop "80x80+1332+208" ^
                -background "#444444" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                submit.png

ECHO * threads.png
%IMAGEMAGICK%   icons_white_1x.png -crop "32x32+273+0" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                threads.png
ECHO * threads_2x.png
%IMAGEMAGICK%   icons_white_2x.png -crop "64x64+546+0" ^
                -background "#222222" -type Grayscale -depth 8 -alpha On ^
                -define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
                threads_2x.png
ECHO.

:: apple-touch-icon
:: =========================================================================================================================
ECHO apple-touch-icon.png
%IMAGEMAGICK%   +antialias -density 648 refs\brightmixIconset_v02.eps -colorspace RGB ^
                -background "#f4f3f5" -resize 2495x1755 -alpha Shape -channel a -negate +channel ^
                -crop "76x76+1196+2" -background "#222222" -gravity Center -extent 192x192 ^
                -type Grayscale -define png:bit-depth=8 -define png:color-type=0 -define png:compression-level=0 ^
                apple-touch-icon.png
ECHO.

:: compress PNGs
:: =========================================================================================================================
:: remove the big temporary files
REM DEL icons_white_1x.png icons_black_1x.png icons_white_2x.png icons_black_2x.png icons_black_half.png

FOR %%F in (*.png) DO (
	ECHO Optimising %%F...
	%PNGOUT% "%%F" /c4 /kbKGD /y /q
	%OPTIPNG% -o7 -clobber -quiet "%%F"
	%PNGCRUSH% -brute -fix -keep bKGD -l 9 -reduce -q "%%F"
	IF %ERRORLEVEL% EQU 0 ERASE "%%F" & REN "pngout.png" "%%F"
)

DEL ..\..\..\apple-touch-icon.default.png
COPY /Y apple-touch-icon.png ..\..\..\apple-touch-icon.default.png

DEL ..\apple-touch-icon.png
MOVE /Y apple-touch-icon.png ..
ECHO.

:: windows 8 Metro icon
:: =========================================================================================================================
:: due to a bug in Windows 8 this icon cannot be 8-bit, it must be 24-bit, so we have to render it after the others have
:: been optimised as the optimisations usually reduce bit-depth

:: having problems with this, see:
:: https://stackoverflow.com/questions/20133627/windows-8-tile-not-displaying-correct-images-fav-icon-displayed-instead

ECHO metro-tile.png
:: for this large icon, we need a high-quality render of the vector. we scale the spritesheet to 720dpi (very large) --
:: note that this is likely to take a while and use a lot of RAM -- then downsize such that the icon we desire is 128x128
%IMAGEMAGICK%   +antialias -density 720 refs/brightmixIconset_v02.eps -colorspace RGB ^
                -background "#ffffff" -resize 3992x2808 -alpha Shape -channel a -negate +channel ^
                -crop "128x128+1912+4" -bordercolor none -border 8 ^
                -define png:bit-depth=8 -define png:color-type=6 -define png:compression-level=0 ^
                metro-tile.png

:: apply some optimisation, whilst retaining the bit-depth
ECHO optimising metro-tile.png...
REM "-nx" preserves bit-depth and colour-type
%OPTIPNG% -o7 -zm1-9 -clobber -quiet -nx metro-tile.png
REM "-bit_depth 8 -c 6" preserves bit-depth and colour-type
%PNGCRUSH% -bit_depth 8 -c 6 -fix -l 9 -q metro-tile.png
IF %ERRORLEVEL% EQU 0 ERASE metro-tile.png & REN pngout.png metro-tile.png

DEL ..\..\..\metro-tile.default.png
COPY /Y metro-tile.png ..\..\..\metro-tile.default.png

DEL ..\metro-tile.png
MOVE /Y metro-tile.png ..

PAUSE