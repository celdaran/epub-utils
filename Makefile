.PHONY: help
help:
	@echo "EPUB Utils is a collection of scripts to help transform EPUB files from a Word export. Usage:"
	@echo ""
	@echo "make renamer: Rename files like 1.xhtml, 23.xhtml, 456.xhtml to 0001.xhtml, 0023.xhtml, 0456.xhtml"
	@echo "make transformer: Transform unzipped EPUB file from Reedsy format to Lockshire format"
	@echo "make toc: Build a new TOC out of already-transformed files"
	@echo "make packager: Build a new package.opf file from already-transformed files"
	@echo ""
	@echo "This is the order they're intended to be run in as well: rename files, transform files, build new TOC+OPF from transformed files"

.PHONY: renamer
renamer:
	php -f renamer.php -- -i /data/www/mbk-cfo/reedsy-input/OEBPS/

.PHONY: transformer
transformer:
	php -f transformer.php -- -i /data/www/mbk-cfo/reedsy-input/OEBPS -o /data/www/mbk-cfo/epub-utils-output

.PHONY: toc
toc:
	php -f toc.php -- -i /data/www/mbk-cfo/epub-utils-output/xhtml/ -o /data/www/mbk-cfo/epub-utils-output/xhtml/generated-toc.xhtml

.PHONY: packager
packager:
	php -f packager.php -- -i ../mbk-cfo/epub-utils-output -o ../mbk-cfo/build/template/EPUB/package.opf
