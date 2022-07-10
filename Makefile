.PHONY: help
help:
	@echo "EPUB Utils is a collection of scripts to help transform EPUB files from a Word export. Usage:"
	@echo ""
	@echo "rename...: Rename files like 1.xhtml, 23.xhtml, 456.xhtml to 0001.xhtml, 0023.xhtml, 0456.xhtml"
	@echo "transform: Transform unzipped EPUB file from Reedsy format to Lockshire format"
	@echo "toc......: Build a new TOC out of already-transformed files"
	@echo "package..: Build a new package.opf file from already-transformed files"
	@echo "export...: Move required files to output for the next stage of processing"
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
	php -f toc.php -- -i ../mbk-cfo/output/xhtml/ -o ../mbk-cfo/output/xhtml/9999.toc.xhtml

.PHONY: package
package:
	php -f packager.php -- -i ../mbk-cfo/output -o ../mbk-cfo/output/package.opf

.PHONY: export
export:
	mkdir -p ../mbk-cfo/output/css
	cp ../mbk-cfo/input/OEBPS/mbk-cfo.css ../mbk-cfo/output/css/manuscript.css
	mkdir -p ../mbk-cfo/output/img
	cp ../mbk-cfo/input/OEBPS/images/* ../mbk-cfo/output/img/
