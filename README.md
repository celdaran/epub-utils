# EPUB Utilities

A collection of highly-customized (read: not generalized) utilties for dealing with EPUB3 generation.

## Utilities

Included utilities are:

* **class-finder.php**: This script finds every `class` attribute in a directory of XHTML files and returns a unique list of discovered classes. This can then be used to compare against the CSS file to make sure all styles are covered.
* **inserter.php**: This script will insert an empty file of the given name into the source directory and renumber all files at or above that number. The file's name must be of the format 9999_your-file-name.xhtml. The number 9999 indicates the insertion point. If there's a file already with that number, its value is bumped up along with every other high-numbered file.
* **ncx.php**: This script reads a TOC generated by `toc.php` and converts it to the EPUB2-supported NCX format. This was required for proper contents-support at KDP.
* **packager.php**: This script combines an OPF template with the output files generated by `transformer.php` and creates a new `package.opf` file. It builds out the `<manifest>` and `<spine>` sections, which could be difficult to manage manaully with hundreds of recipes.
* **qr-linker.php**: This script post-processes the XHTML directory by combining found recipe titles with a CSV file of URLs for the embedded qr codes.
* **renamer.php**: This script renames the list of Calibre-generated chapter files so that they sort numerically. That is, files like 1, 2, 34, and 101 will be renamed to 0001, 0002, 0034, 0101 respectively.
* **toc.php**: This script generates an EPUB3-compatible table of contents document.
* **transformer.php**: This script reads a directory of a Calibre-genenerated EPUB file (unzipped, of course) and converts it to a format to match my own EPUB structure. Things like `<p>Stats</p>` get turned into `<h3 class="stats-header">Stats</h3>` and the stats themselves go from `<p>25 Minutes</p>` to `<li class="stats">`. Additionally, a new style sheet is moved into place and image assets are given new names.

## Usage

The scripts require SOURCE and/or TARGET environment variables to be defined.

```bash
SOURCE=../mbk-cfo/source/OEBPS make rename
SOURCE=../mbk-cfo/source/OEBPS TARGET=../mbk-cfo/target/OEBPS make install
SOURCE=../mbk-cfo/source/OEBPS TARGET=../mbk-cfo/target/OEBPS make transform 
TARGET=../mbk-cfo/target/OEBPS make toc
TARGET=../mbk-cfo/target/OEBPS make package
```

A quick full rebuild, after an initial build has been done:

```bash
SOURCE=../mbk-cfo/source/OEBPS TARGET=../mbk-cfo/target/OEBPS make install transform toc package
```
