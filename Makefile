.PHONY: help
help:
	@echo "EPUB Utils is a collection of scripts to help transform EPUB files from a Word export. Usage:"
	@echo ""
	@echo "Main targets:"
	@echo "  rename........: Rename files like 1.xhtml, 23.xhtml, 456.xhtml to 0001.xhtml, 0023.xhtml, 0456.xhtml"
	@echo "  install.......: Copy css and images from source to target (no transformation)"
	@echo "  transform.....: Transform unzipped EPUB file from Reedsy format to Lockshire format"
	@echo "  toc...........: Build a new TOC out of already-transformed files"
	@echo "  package.......: Build a new package.opf file from already-transformed files"
	@echo ""
	@echo "Utility targets:"
	@echo "  class-finder..: Finds all CSS classes referenced in a directory"
	@echo "  qr-linker.....: Finds all QR codes in the documents and enhances them with anchor tags"
	@echo ""
	@echo "The intended run order is: make rename transform toc package export"
	@echo "The insert and remove targets are just helpers and possible not needed."

.PHONY: rename
rename: check-source
	php -f src/renamer.php -- -i $(SOURCE)

.PHONY: insert
insert: check-file check-source
	php -f src/inserter.php -- -i $(SOURCE) -f $(FILE)

.PHONY: install
install: check-source check-target
	mkdir -p $(TARGET)/css
	mkdir -p $(TARGET)/images
	mkdir -p $(TARGET)/fonts
	rm -f $(TARGET)/css/*
	rm -f $(TARGET)/images/*
	rm -f $(TARGET)/fonts/*
	cp $(SOURCE)/manuscript.css $(TARGET)/css/manuscript.css
	cp $(SOURCE)/images/* $(TARGET)/images/
	cp $(SOURCE)/fonts/* $(TARGET)/fonts/
	cp $(SOURCE)/ebook-cover.jpg $(TARGET)/

.PHONY: transform
transform: check-source check-target
	mkdir -p $(TARGET)/xhtml
	rm -f $(TARGET)/xhtml/*
	php -f src/transformer.php -- -i $(SOURCE) -o $(TARGET)

.PHONY: toc
toc: check-target
	php -f src/toc.php -- -i $(TARGET)/xhtml -o $(TARGET)/xhtml/0005.contents.xhtml

.PHONY: package
package: check-target
	php -f src/packager.php -- -i $(TARGET) -o $(TARGET)/package.opf

.PHONY: class-finder
class-finder: check-source
	php -f src/class-finder.php -- -i $(SOURCE)

.PHONY: qr-linker
qr-linker: check-source
	php -f src/qr-linker.php -- -i $(SOURCE)

.PHONY: check-source
check-source:
ifndef SOURCE
	$(error SOURCE is undefined)
endif

.PHONY: check-target
check-target:
ifndef TARGET
	$(error TARGET is undefined)
endif

.PHONY: check-file
check-file:
ifndef FILE
	$(error FILE is undefined)
endif
