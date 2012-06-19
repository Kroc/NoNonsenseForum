#!/usr/bin/env bash

# this is a bash shell script for running on Mac, Linux and other UNIX-like OSes

# software to install:
# 	imagemagick / ghostscript
# 	pngout
#	optipng
#	pngcrush

# change directory, to the directory of this script
# (because double clicking on this script may use home directory instead)
cd "`dirname "$0"`"

# white 1x
# ==========================================================================================================================
# convert the vector EPS to PNG, masking the alpha.
# the antialias applied by default is no good, so we do it manually by resizing a very hires image down
echo "icons white 1x..."
convert +antialias -density 648 refs/brightmixIconset_v02.eps -colorspace RGB \
	-background "#f4f3f5" -resize 998x702 -alpha Shape -channel a -negate \
	icons_white_1x.png

# crop and save the individual icons needed
# --------------------------------------------------------------------------------------------------------------------------
# some optimisations we apply:
# 	`-background "#eeeeee"`		IE6 doesn’t support transparency so we add a useful background colour to fallback on
#	`-depth 8`			convert source image to 8-bit depth (255-colours)
#	`-define png:depth=8`		and output as 8-bit too
#	`-define png:compression...`	don’t compress, we’re going to optimise all the PNGs compression afterwards

echo "deletetitle.png"
convert icons_white_1x.png -crop "32x32+625+270" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	deletetitle.png

echo "folders.png"
convert icons_white_1x.png -crop "32x32+478+54" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	folders.png

echo "help.png"
convert icons_white_1x.png -crop "32x32+273+432" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	help.png

# echo "logo.png"
# convert icons_white_1x.png -crop "32x32+478+0" \
# 	-background "#222222" -type Grayscale -depth 8 -alpha On \
# 	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
# 	logo.png

echo "new.png"
convert icons_white_1x.png -crop "32x32+273+54" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	new.png

echo "replies.png"
convert icons_white_1x.png -crop "32x32+273+487" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	replies.png

# echo "submit.png"
# convert icons_white_1x.png -crop "40x40+666+104" \
# 	-background "#444444" -type Grayscale -depth 8 -alpha On \
# 	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
# 	submit.png

echo "threads.png"
convert icons_white_1x.png -crop "32x32+273+0" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	threads.png

# black 1x
# ==========================================================================================================================
# invert to produce the black set
echo "icons black 1x..."
convert icons_white_1x.png -negate icons_black_1x.png

echo "add.png"
convert icons_black_1x.png -crop "16x16+875+152" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	add.png

echo "delete.png"
convert icons_black_1x.png -crop "16x16+875+177" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	delete.png

# echo "go.png"
# convert icons_black_1x.png -crop "20x20+676+114" \
# 	-background "#ffffff" -type Grayscale -depth 8 -alpha On \
# 	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
# 	go.png

echo "here.png"
convert icons_black_1x.png -crop "16x20+979+58" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	here.png

# echo "lang.png"
# convert icons_black_1x.png -crop "20x20+978+117" \
# 	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
# 	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
# 	lang.png

echo "rss.png"
convert icons_black_1x.png -crop "16x16+182+62" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	rss.png

echo "search.png"
convert icons_black_1x.png -crop "13x17+536+224" \
	-background "#ffffff" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	search.png

# echo "sticky.png"
# convert icons_black_1x.png -crop "16x16+181+223" \
# 	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
# 	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
# 	sticky.png

# white 2x
# ==========================================================================================================================
# produce a version double the size for hi-DPI screens
echo "icons white 2x..."
convert +antialias -density 648 refs/brightmixIconset_v02.eps -colorspace RGB \
	-background "#f4f3f5" -resize 1996x1404 -alpha Shape -channel a -negate \
	icons_white_2x.png

echo "deletetitle_2x.png"
convert icons_white_2x.png -crop "64x64+1250+540" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	deletetitle_2x.png

echo "folders_2x.png"
convert icons_white_2x.png -crop "64x64+956+108" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	folders_2x.png

echo "help_2x.png"
convert icons_white_2x.png -crop "64x64+546+864" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	help_2x.png

echo "logo.png (2x)"
convert icons_white_2x.png -crop "64x64+956+0" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	logo.png

echo "new_2x.png"
convert icons_white_2x.png -crop "64x64+546+108" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	new_2x.png

echo "replies_2x.png"
convert icons_white_2x.png -crop "64x64+546+974" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	replies_2x.png

echo "submit.png (2x)"
convert icons_white_2x.png -crop "80x80+1332+208" \
	-background "#444444" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	submit.png

echo "threads_2x.png"
convert icons_white_2x.png -crop "64x64+546+0" \
	-background "#222222" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	threads_2x.png

# black 2x
# ==========================================================================================================================
# invert to produce the black 2x set
echo "icons black 2x..."
convert icons_white_2x.png -negate icons_black_2x.png

echo "add_2x.png"
convert icons_black_2x.png -crop "32x32+1750+304" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	add_2x.png

echo "delete_2x.png"
convert icons_black_2x.png -crop "32x32+1750+354" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	delete_2x.png

echo "go.png (2x)"
convert icons_black_2x.png -crop "40x40+1352+228" \
	-background "#ffffff" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	go.png

echo "here_2x.png"
convert icons_black_2x.png -crop "32x40+1958+116" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	here_2x.png

echo "lang.png (2x)"
convert icons_black_2x.png -crop "40x40+1956+234" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	lang.png

echo "rss_2x.png"
convert icons_black_2x.png -crop "32x32+364+124" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	rss_2x.png

echo "search_2x.png"
convert icons_black_2x.png -crop "26x34+1072+448" \
	-background "#ffffff" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	search_2x.png

echo "sticky.png (2x)"
convert icons_black_2x.png -crop "32x32+362+446" \
	-background "#eeeeee" -type Grayscale -depth 8 -alpha On \
	-define png:bit-depth=8 -define png:color-type=4 -define png:compression-level=0 \
	sticky.png

# apple-touch-icon
echo "apple-touch-icon"
convert +antialias -density 648 refs/brightmixIconset_v02.eps -colorspace RGB \
	-background "#f4f3f5" -resize 2495x1755 -alpha Shape -channel a -negate +channel \
	-crop "76x76+1196+2" -background "#222222" -gravity Center -extent 144x144 \
	apple-touch-icon.png

# compress PNGs
# ==========================================================================================================================
# remove the big temporary files
rm icons_white_1x.png icons_black_1x.png icons_white_2x.png icons_black_2x.png

for FILE in *.png
do
	echo "optimising $FILE..."
	pngout $FILE -c4 -kbKGD -y -q
	optipng -o7 -zm1-9 -clobber -quiet $FILE
	pngcrush -brute -keep bKGD -l 9 -ow -reduce -q
done

rm ../apple-touch-icon.png
mv apple-touch-icon.png ..
