#!/bin/bash
echo "Installing base"
rm -rf ../mbk-cfo/target/
SOURCE=../mbk-cfo/source/OEBPS TARGET=../mbk-cfo/target/OEBPS make install
sleep 1

echo "Running XHTML transformations"
SOURCE=../mbk-cfo/source/OEBPS TARGET=../mbk-cfo/target/OEBPS make transform
sleep 1

#echo "Building TOC"
#TARGET=../mbk-cfo/target/OEBPS make toc
#sleep 1

echo "Building OPF"
TARGET=../mbk-cfo/target/OEBPS make package
sleep 1

echo "Generating QR Code links"
SOURCE=../mbk-cfo/target/OEBPS/xhtml make qr-linker
