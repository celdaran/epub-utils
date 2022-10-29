#!/bin/bash

if [[ -z "${PROJECT_NAME}" ]]; then
  echo "Must define PROJECT_NAME environment variable"
  echo "This should match the github repo name of the book (e.g., mbk-cfo)"
  exit 1
fi

echo "Installing base"
rm -rf ../$PROJECT_NAME/target/
SOURCE=../$PROJECT_NAME/source/OEBPS TARGET=../mbk-cfo/target/OEBPS make install
sleep 1

echo "Running XHTML transformations"
SOURCE=../$PROJECT_NAME/source/OEBPS TARGET=../mbk-cfo/target/OEBPS make transform
sleep 1

#echo "Building TOC"
#TARGET=../$PROJECT_NAME/target/OEBPS make toc
#sleep 1

echo "Building OPF"
TARGET=../$PROJECT_NAME/target/OEBPS make package
sleep 1

echo "Generating QR Code links"
SOURCE=../$PROJECT_NAME/target/OEBPS/xhtml make qr-linker
