# JSZip2

[![npm](https://img.shields.io/npm/dw/jszip2.svg)](https://npmjs.com/package/jszip2)
[![NpmVersion](https://img.shields.io/npm/v/jszip2.svg)](https://npmjs.com/package/jszip2)
[![NpmLicense](https://img.shields.io/npm/l/jszip2.svg)](https://npmjs.com/package/jszip2)
[![Travis (.org) branch](https://img.shields.io/travis/DigiExam/jszip2/master.svg?logo=travis)](https://travis-ci.org/DigiExam/jszip2)

=====


Semi-maintained version of: https://github.com/Stuk/jszip

A library for creating, reading and editing .zip files with JavaScript, with a
lovely and simple API.

## Documentation

See https://stuk.github.io/jszip for all the documentation.

## Change log

See [CHANGES.md](CHANGES.md)

## Example usage

```javascript
var zip = new JSZip();

zip.file("Hello.txt", "Hello World\n");

var img = zip.folder("images");
img.file("smile.gif", imgData, {base64: true});

zip.generateAsync({type:"blob"}).then(function(content) {
    // see FileSaver.js
    saveAs(content, "example.zip");
});

/*
Results in a zip containing
Hello.txt
images/
    smile.gif
*/
```

## License

JSZip is dual-licensed. You may use it under the MIT license *or* the GPLv3
license. See [LICENSE.markdown](LICENSE.markdown).

Original author: Stuart Knightley
