# fhir-qr-generator

QR Code Generator project.

## Prerequisites
* [php](https://www.php.net/downloads.php)
* [DotNet SDK](https://aka.ms/dotnet-download)
* [peachpie Visual studio code extension](https://marketplace.visualstudio.com/items?itemName=iolevel.peachpie-vscode)
* [C# Visual studio code extension](https://marketplace.visualstudio.com/items?itemName=ms-dotnettools.csharp)

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