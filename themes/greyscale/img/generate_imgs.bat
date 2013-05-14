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

:: white 1x
:: =========================================================================================================================
:: convert the vector EPS to PNG, masking the alpha.
:: the antialias applied by default is no good, so we do it manually by resizing a very hires image down
IF NOT EXIST icons_white_1x.png (
	ECHO icons white 1x...
	convert +antialias -density 648 refs\brightmixIconset_v02.eps -colorspace RGB ^
		-background "#f4f3f5" -resize 998x702 -alpha Shape -channel a -negate ^
		icons_white_1x.png
)

:: crop and save the individual icons needed
:: -------------------------------------------------------------------------------------------------------------------------
:: some optimisations we apply:
:: 	`-background "#eeeeee"`		IE6 doesn’t support transparency so we add a useful background colour to fallback on
::	`-depth 8`			convert source image to 8-bit depth (255-colours)
::	`-define png:depth=8`		and output as 8-bit too
::	`-define png:compression...`	don’t compress, we’re going to optimise all the PNGs compression afterwards

ECHO * deletetitle.png
convert icons_white_1x.png -crop "32x32+625+270" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	deletetitle.png

ECHO * folders.png
convert icons_white_1x.png -crop "32x32+478+54" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	folders.png

ECHO * help.png
convert icons_white_1x.png -crop "32x32+273+432" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	help.png

ECHO * new.png
convert icons_white_1x.png -crop "32x32+273+54" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	new.png

ECHO * replies.png
convert icons_white_1x.png -crop "32x32+273+487" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	replies.png

ECHO * threads.png
convert icons_white_1x.png -crop "32x32+273+0" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	threads.png

:: black 1x
:: =========================================================================================================================
:: invert to produce the black set
IF NOT EXIST icons_black_1x.png (
	ECHO. & ECHO icons black 1x...
	convert icons_white_1x.png -negate icons_black_1x.png
)

ECHO * add.png
convert icons_black_1x.png -crop "16x16+875+152" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	add.png

REM ECHO * delete.png
REM convert icons_black_1x.png -crop "16x16+875+177" ^
REM	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
REM	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
REM	delete.png

ECHO * here.png
convert icons_black_1x.png -crop "16x20+979+58" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	here.png

ECHO * rss.png
convert icons_black_1x.png -crop "16x16+182+62" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	rss.png

ECHO * search.png
convert icons_black_1x.png -crop "13x17+536+224" ^
	-background "#ffffff" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	search.png

:: the append icon is only available in large size (default:32x32), create a ½-size image so that we can get a 16x16 icon
IF NOT EXIST icons_black_half.png (
	ECHO (icons black half-size...)
	convert +antialias -density 144 refs\brightmixIconset_v02.eps -colorspace RGB ^
		-background "#f4f3f5" -resize 499x351 -alpha Shape -channel a -negate ^
		icons_black_half.png
	convert icons_black_half.png -negate icons_black_half.png
)

ECHO * append.png
convert icons_black_half.png -crop "16x16+137+27" ^
	-background "#888888" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	append.png

ECHO * delete.png
convert icons_black_half.png -crop "16x16+312+135" ^
	-background "#888888" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	delete.png

:: white 2x
:: =========================================================================================================================
:: produce a version double the size for hi-DPI screens
IF NOT EXIST icons_white_2x.png (
	ECHO. & ECHO icons white 2x...
	convert +antialias -density 648 refs\brightmixIconset_v02.eps -colorspace RGB ^
		-background "#f4f3f5" -resize 1996x1404 -alpha Shape -channel a -negate ^
		icons_white_2x.png
)

ECHO * deletetitle_2x.png
convert icons_white_2x.png -crop "64x64+1250+540" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	deletetitle_2x.png

ECHO * folders_2x.png
convert icons_white_2x.png -crop "64x64+956+108" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	folders_2x.png

ECHO * help_2x.png
convert icons_white_2x.png -crop "64x64+546+864" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	help_2x.png

ECHO * logo.png (2x)
convert icons_white_2x.png -crop "64x64+956+0" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	logo.png

ECHO * new_2x.png
convert icons_white_2x.png -crop "64x64+546+108" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	new_2x.png

ECHO * replies_2x.png
convert icons_white_2x.png -crop "64x64+546+974" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	replies_2x.png

ECHO * submit.png (2x)
convert icons_white_2x.png -crop "80x80+1332+208" ^
	-background "#444444" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	submit.png

ECHO * threads_2x.png
convert icons_white_2x.png -crop "64x64+546+0" ^
	-background "#222222" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	threads_2x.png

:: black 2x
:: =========================================================================================================================
:: invert to produce the black 2x set
IF NOT EXIST icons_black_2x.png (
	ECHO. & ECHO icons black 2x...
	convert icons_white_2x.png -negate icons_black_2x.png
)

ECHO * add_2x.png
convert icons_black_2x.png -crop "32x32+1750+304" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	add_2x.png

REM ECHO * delete_2x.png
REM convert icons_black_2x.png -crop "32x32+1750+354" ^
REM	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
REM	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
REM	delete_2x.png

ECHO * go.png (2x)
convert icons_black_2x.png -crop "40x40+1352+228" ^
	-background "#ffffff" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	go.png

ECHO * here_2x.png
convert icons_black_2x.png -crop "32x40+1958+116" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	here_2x.png

ECHO * lang.png (2x)
convert icons_black_2x.png -crop "40x40+1956+234" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	lang.png

ECHO * rss_2x.png
convert icons_black_2x.png -crop "32x32+364+124" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	rss_2x.png

ECHO * search_2x.png
convert icons_black_2x.png -crop "26x34+1072+448" ^
	-background "#ffffff" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	search_2x.png

ECHO * sticky.png (2x)
convert icons_black_2x.png -crop "32x32+362+446" ^
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	sticky.png

::the 2x append / delete icon (32x32) actually comes from the 1x sheet
ECHO * append_2x.png
convert icons_black_1x.png -crop "32x32+273+54" ^
	-background "#888888" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	append_2x.png

ECHO * delete_2x.png
convert icons_black_1x.png -crop "32x32+625+270" ^
	-background "#888888" -type Grayscale -depth 8 -alpha On ^
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 ^
	delete_2x.png

:: apple-touch-icon
ECHO. & ECHO apple-touch-icon.png
convert +antialias -density 648 refs\brightmixIconset_v02.eps -colorspace RGB ^
	-background "#f4f3f5" -resize 2495x1755 -alpha Shape -channel a -negate +channel ^
	-crop "76x76+1196+2" -background "#222222" -gravity Center -extent 144x144 ^
	-type Grayscale -define png:bit-depth=8 -define png:color-type=0 -define png:compression-level=0 ^
	apple-touch-icon.png

:: compress PNGs
:: =========================================================================================================================
:: remove the big temporary files
DEL icons_white_1x.png icons_black_1x.png icons_white_2x.png icons_black_2x.png icons_black_half.png

FOR %%F in (*.png) DO (
	ECHO Optimising %%F...
	bin\pngout.exe "%%F" /c4 /kbKGD /y /q
	bin\optipng.exe -o7 -clobber -quiet "%%F"
	bin\pngcrush.exe -brute -fix -keep bKGD -l 9 -reduce -q "%%F"
	IF %ERRORLEVEL% EQU 0 ERASE "%%F" & REN "pngout.png" "%%F"
)

DEL ..\..\..\apple-touch-icon.default.png
COPY /Y apple-touch-icon.png ..\..\..\apple-touch-icon.default.png

DEL ..\apple-touch-icon.png
MOVE /Y apple-touch-icon.png ..

:: windows 8 tile icon
:: due to a bug in Windows 8 this icon cannot be 8-bit, it must be 24-bit, so we have to render it after the others have
:: been optimised as the optimisations usually reduce bit-depth
ECHO. & ECHO metro-tile.png
convert +antialias -density 720 refs/brightmixIconset_v02.eps -colorspace RGB ^
	-background "#ffffff" -resize 3992x2808 -alpha Shape -channel a -negate +channel ^
	-crop "128x128+1912+4" -bordercolor none -border 8 ^
	-define png:bit-depth=8 -define png:color-type=6 -define png:compression-level=0 ^
	metro-tile.png

:: apply some optimisation, whilst retaining the bit-depth
ECHO optimising metro-tile.png...
REM "-nx" preserves bit-depth and colour-type
bin\optipng.exe -o7 -zm1-9 -clobber -quiet -nx metro-tile.png
REM "-bit_depth 8 -c 6" preserves bit-depth and colour-type
bin\pngcrush.exe -bit_depth 8 -c 6 -fix -l 9 -q metro-tile.png
IF %ERRORLEVEL% EQU 0 ERASE metro-tile.png & REN pngout.png metro-tile.png

DEL ..\..\..\metro-tile.default.png
COPY /Y metro-tile.png ..\..\..\metro-tile.default.png

DEL ..\metro-tile.png
MOVE /Y metro-tile.png ..

PAUSE