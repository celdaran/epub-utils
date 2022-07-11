.PHONY: help
help:
	@echo "EPUB Utils is a collection of scripts to help transform EPUB files from a Word export. Usage:"
	@echo ""
	@echo "rename...: Rename files like 1.xhtml, 23.xhtml, 456.xhtml to 0001.xhtml, 0023.xhtml, 0456.xhtml"
	@echo "insert...: Add a file and renumber all subsequent files"
	@echo "remove...: Remove a file and renumber all subsequent files"
	@echo "install..: Copy css and images from source to target (no transformation)"
	@echo "transform: Transform unzipped EPUB file from Reedsy format to Lockshire format"
	@echo "toc......: Build a new TOC out of already-transformed files"
	@echo "package..: Build a new package.opf file from already-transformed files"
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
	mkdir -p $(TARGET)/img
	rm $(TARGET)/css/*
	rm $(TARGET)/img/*
	cp $(SOURCE)/manuscript.css $(TARGET)/css/manuscript.css
	cp $(SOURCE)/images/* $(TARGET)/img/

.PHONY: transform
transform: check-source check-target
	mkdir -p $(TARGET)/xhtml
	rm $(TARGET)/xhtml/*
	php -f src/transformer.php -- -i $(SOURCE) -o $(TARGET)

.PHONY: toc
toc: check-target
	php -f src/toc.php -- -i $(TARGET)/xhtml -o $(TARGET)/xhtml/0005.contents.xhtml

.PHONY: package
package: check-target
	php -f src/packager.php -- -i $(TARGET) -o $(TARGET)/package.opf

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
