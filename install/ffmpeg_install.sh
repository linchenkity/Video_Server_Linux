#!/bin/bash
#
# FFmpeg Build Script
# By haha_Dashen
#
echo -e "\033[1;33m[FFmpeg Install Script] \033[0m"
echo -e "\033[1;37m[Build By:haha_Dashen]"
echo -e "[CDN By PackCDN]\033[0m"
ntpdate 0.asia.pool.ntp.org
time_start=$(date +%s)
cd /usr/local/src
echo -e "\033[0;34m+Install Base Software\033[0m"
sleep 2
yum install -y autoconf automake bzip2 cmake freetype-devel gcc gcc-c++ git libtool make mercurial pkgconfig zlib-devel
mkdir ~/ffmpeg_sources
################################################NASM
echo -e "\033[0;34m+Install NASM\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/nasm-2.13.02.tar.bz2
sleep 2
tar xjvf nasm-2.13.02.tar.bz2
cd nasm-2.13.02
./autogen.sh
./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin"
make
make install
################################################YASM
echo -e "\033[0;34m+Install YASM\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/yasm-1.3.0.tar.gz
sleep 2
tar xzvf yasm-1.3.0.tar.gz
cd yasm-1.3.0
./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin"
make
make install
################################################X264
echo -e "\033[0;34m+Install X264 Encoder\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/last_x264.tar.bz2
sleep 2
tar xjvf last_x264.tar.bz2
cd x264-snapshot-20180415-2245
PKG_CONFIG_PATH="$HOME/ffmpeg_build/lib/pkgconfig" ./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin" --enable-static
make
make install
################################################X265
echo -e "\033[0;34m+Install X265 Encoder\033[0m"
sleep 2
cd ~/ffmpeg_sources
hg clone https://bitbucket.org/multicoreware/x265
cd ~/ffmpeg_sources/x265/build/linux
cmake -G "Unix Makefiles" -DCMAKE_INSTALL_PREFIX="$HOME/ffmpeg_build" -DENABLE_SHARED:bool=off ../../source
make
make install
################################################AAC
echo -e "\033[0;34m+Install AAC Encoder\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/fdk-aac-0.1.6.tar.gz
sleep 2
tar xzvf fdk-aac-0.1.6.tar.gz
cd fdk-aac-0.1.6
autoreconf -fiv
./configure --prefix="$HOME/ffmpeg_build" --disable-shared
make
make install
################################################LAME
echo -e "\033[0;34m+Install MP3 Encoder\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/lame-3.100.tar.gz
sleep 2
tar xzvf lame-3.100.tar.gz
cd lame-3.100
./configure --prefix="$HOME/ffmpeg_build" --bindir="$HOME/bin" --disable-shared --enable-nasm
make
make install
################################################OPUS
echo -e "\033[0;34m+Install Opus Encoder\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/opus-1.2.1.tar.gz
sleep 2
tar xzvf opus-1.2.1.tar.gz
cd opus-1.2.1
./configure --prefix="$HOME/ffmpeg_build" --disable-shared
make
make install
################################################OGG
echo -e "\033[0;34m+Install OGG Encoder\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/libogg-1.3.3.tar.gz
sleep 2
tar xzvf libogg-1.3.3.tar.gz
cd libogg-1.3.3
./configure --prefix="$HOME/ffmpeg_build" --disable-shared
make
make install
################################################Vorbis
echo -e "\033[0;34m+Install Vorbis Encoder\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/libvorbis-1.3.5.tar.gz
sleep 2
tar xzvf libvorbis-1.3.5.tar.gz
cd libvorbis-1.3.5
./configure --prefix="$HOME/ffmpeg_build" --with-ogg="$HOME/ffmpeg_build" --disable-shared
make
make install
################################################VPX
echo -e "\033[0;34m+Install Vpx Encoder\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
mkdir libvpx
curl -O -L http://private.cloud.packcdn.com/video_server_linux/libvpx-master.tar.gz
sleep 2
tar xzvf libvpx-master.tar.gz -C libvpx
cd libvpx
./configure --prefix="$HOME/ffmpeg_build" --disable-examples --disable-unit-tests --enable-vp9-highbitdepth --as=yasm
make
make install
################################################FFmpeg
echo -e "\033[0;34m+Install FFmpeg\033[0m"
cd ~/ffmpeg_sources
echo -e "\033[1;37mPackCDN Provide This File Download\033[0m"
curl -O -L http://private.cloud.packcdn.com/video_server_linux/ffmpeg-3.4.2.tar.bz2
sleep 2
tar xjvf ffmpeg-3.4.2.tar.bz2
cd ffmpeg-3.4.2
PATH="$HOME/bin:$PATH" PKG_CONFIG_PATH="$HOME/ffmpeg_build/lib/pkgconfig" ./configure \
  --prefix="$HOME/ffmpeg_build" \
  --pkg-config-flags="--static" \
  --extra-cflags="-I$HOME/ffmpeg_build/include" \
  --extra-ldflags="-L$HOME/ffmpeg_build/lib" \
  --extra-libs=-lpthread \
  --extra-libs=-lm \
  --bindir="$HOME/bin" \
  --enable-gpl \
  --enable-libfdk_aac \
  --enable-libfreetype \
  --enable-libmp3lame \
  --enable-libopus \
  --enable-libvorbis \
  --enable-libvpx \
  --enable-libx264 \
  --enable-libx265 \
  --enable-nonfree
make
make install
hash -r
time_end=$(date +%s)
total_time=$(($time_end - time_start))
echo -e "\033[1;33mDone! $total_time Seconds Used\033[0m"