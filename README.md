
# fhir-qr-generator

  

QR Code Generator project.

  

## Prerequisites

  

- [php 7.2.30](https://www.php.net/downloads.php)

- [composer 2.2.18](https://getcomposer.org/download/)

- [DotNet SDK](https://aka.ms/dotnet-download)

- [peachpie Visual studio code extension](https://marketplace.visualstudio.com/items?itemName=iolevel.peachpie-vscode)

- [C# Visual studio code extension](https://marketplace.visualstudio.com/items?itemName=ms-dotnettools.csharp)

- [qrcode](https://www.npmjs.com/package/qrcode)

- [openssl](https://www.openssl.org/source)

- mbstring php extension enabled

  

**Note:**<br>

Install the QRcode and OpenSSL packages globally. Since PeachPie cannot support some of the functions during compilation at the C# environment this project will utilize the command line when creating key pairs and QRcode through thru PHP **exec** function.

  

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

  
  ## PHP Requirements
  Enable mbstring extension
  1.  Navigate to the folder where your php is located. **(Default is C:\ Drive)**
  2.  Locate php.ini file and open it with any notepad or editing software.
  3.  Your php ini file should look like this below.

  ```
;extension=bz2
;extension=curl
;extension=fileinfo
;extension=gd2
;extension=gettext
;extension=gmp
;extension=intl
;extension=imap
;extension=interbase
;extension=ldap
extension=mbstring
;extension=exif      ; Must be after mbstring as it depends on it
;extension=mysqli
;extension=oci8_12c  ; Use with Oracle Database 12c Instant Client
;extension=odbc
;extension=openssl
;extension=pdo_firebird
;extension=pdo_mysql
;extension=pdo_oci
;extension=pdo_odbc
;extension=pdo_pgsql
;extension=pdo_sqlite
;extension=pgsql
;extension=shmop
```


## PHP Usage
1. Clone whole project.

2. Navigate to src/HealthDataManager.php and change the variables listed below to your own data.
	<br>&nbsp;&nbsp; ***Variables:*** 
<br>&nbsp;&nbsp;    $user_id : User ID used for QR
 <br> &nbsp;&nbsp; $storage->path = the folder where you want it to output the QR
 <br> &nbsp;&nbsp; $filename = the name of the json file and it **must** be placed inside **/fhir_json** folder within the project
>
  &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; File location is on src/HealthDataManager.php, this part of the code is at the bottom of the file.
  ```
// For testing purposes in PHP, you can change the path to your current environment.
// $storage = new stdClass;
// $storage->path = "C:\Users\clize\Desktop\qr";

// $user_id = "user-test";

// $manager = new HealthDataManager($storage);
// $manager->createEncKeyPair($user_id);
// $keys= $manager->getEncKeyPair($user_id);
// $manager->setSigPrivateKey("test",$keys["private_key"]);

// $filename = __DIR__ . "/fhir_json/qrCodeLatest.fhir.json";
// $data = file_get_contents($filename);
// $results = $manager->generateHealthDataQr($user_id, $data);
// print_r($results);
```

3.  Uncomment or remove **//** from the code
 Now it should look like this
  ```
// For testing purposes in PHP, you can change the path to your current environment.
$storage = new stdClass;
$storage->path = "C:\Users\clize\Desktop\qr";

$user_id = "user-test";

$manager = new HealthDataManager($storage);
$manager->createEncKeyPair($user_id);
$keys= $manager->getEncKeyPair($user_id);
$manager->setSigPrivateKey("test",$keys["private_key"]);

$filename = __DIR__ . "/fhir_json/qrCodeLatest.fhir.json";
$data = file_get_contents($filename);
$results = $manager->generateHealthDataQr($user_id, $data);
print_r($results);
```
4. After doing all the changes to HealthDataManager.php.
 Open a terminal and cd into the project folder and run the edited file using command below.
 `php src/HealthDataManager.php`