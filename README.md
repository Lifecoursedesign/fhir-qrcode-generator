# fhir-qr-generator

QR Code Generator project.

## Prerequisites

- [php 8.0](https://www.php.net/downloads.php)
- [DotNet SDK](https://aka.ms/dotnet-download)
- [peachpie Visual studio code extension](https://marketplace.visualstudio.com/items?itemName=iolevel.peachpie-vscode)
- [C# Visual studio code extension](https://marketplace.visualstudio.com/items?itemName=ms-dotnettools.csharp)
- [qrcode](https://www.npmjs.com/package/qrcode)
- [openssl](https://www.openssl.org/source)

**Note:**<br>
Install the QRcode and OpenSSL packages globally.  Since PeachPie cannot support some of the functions during compilation at the C# environment this project will utilize the command line when creating key pairs and QRcode through thru PHP **exec** function.

## Peachpie setup

After installing prerequisites, Run the following commands in Visual Studio code terminal in order to setup peachpie

```python
# add source list
dotnet nuget add source https://api.nuget.org/v3/index.json -n "nuget.org source"

# restore project
dotnet restore
```

## Usage

1. Build/Compile php code to dll.
   ```sh
      dotnet build
   ```
