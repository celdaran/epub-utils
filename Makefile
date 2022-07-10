.PHONY: help
help:
	@echo "EPUB Utils is a collection of scripts to help transform EPUB files from a Word export. Usage:"
	@echo ""
	@echo "rename...: Rename files like 1.xhtml, 23.xhtml, 456.xhtml to 0001.xhtml, 0023.xhtml, 0456.xhtml"
	@echo "transform: Transform unzipped EPUB file from Reedsy format to Lockshire format"
	@echo "toc......: Build a new TOC out of already-transformed files"
	@echo "package..: Build a new package.opf file from already-transformed files"
	@echo ""
	@echo "This is the order they're intended to be run in as well: rename files, transform files, build new TOC+OPF from transformed files"

.PHONY: rename
rename:
	php -f renamer.php -- -i ../mbk-cfo/input/OEBPS/

.PHONY: transform
transform:
	mkdir -p ../mbk-cfo/output/xhtml
	php -f transformer.php -- -i ../mbk-cfo/input/OEBPS -o ../mbk-cfo/output

.PHONY: toc
toc:
	php -f toc.php -- -i /data/www/mbk-cfo/epub-utils-output/xhtml/ -o /data/www/mbk-cfo/epub-utils-output/xhtml/generated-toc.xhtml

.PHONY: packager
packager:
	php -f packager.php -- -i ../mbk-cfo/epub-utils-output -o ../mbk-cfo/build/template/EPUB/package.opf
