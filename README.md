# Laravel CI/CD dengan Deployer dan Github Action [Bahasa Indonesia]

![CI CD](https://github.com/yogameleniawan/laravel-cicd-deployer/assets/64576201/692d27d8-c6bc-4447-91ae-5ea500757e7f)


## Daftar Isi
  - [Daftar Isi](#daftar-isi)
  - [Tutorial](#tutorial)
  - [Resources](#resources)
  - [Copyright](#copyright)

## Tutorial
1. Install Laravel
```
composer create-project laravel/laravel example-app
```
2. Install Deployer
```
composer require --dev deployer/deployer
```
3. Jalankan Perintah pada terminal kemudian pilih PHP
```
vendor/bin/dep init
```
4. Ubah file deployer.php 
```
<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'contrib/npm.php';

set('bin/php', function () {
    return '/usr/local/bin/php'; // change
});

// HARUS DIGANTI SESUAI KEBUTUHAN ANDA
set('application', 'Nama Aplikasi'); 
set('repository', 'SSH_GIT_CLONE'); // Git Repository contoh set('repository', 'git@github.com:yogameleniawan/laravel-cicd-deployer.git');
// HARUS DIGANTI SESUAI KEBUTUHAN ANDA

set('git_tty', true);
set('git_ssh_command', 'ssh -o StrictHostKeyChecking=no');

set('keep_releases', 5);

set('writable_mode', 'chmod'); // shared hosting

// Shared files/dirs between deploys
add('shared_files', ['.env']);
add('shared_dirs', ['storage']);

// Writable dirs by web server
add('writable_dirs', [
    "bootstrap/cache",
    "storage",
    "storage/app",
    "storage/framework",
    "storage/logs",
]);

set('composer_options', '--verbose --prefer-dist --no-progress --no-interaction --no-dev --optimize-autoloader');

// Hosts

// HARUS DIGANTI SESUAI KEBUTUHAN ANDA

host('NAMA_REMOTE_HOST') // Nama remote host server ssh anda | contoh host('NAMA_REMOTE_HOST')
->setHostname('NAMA_HOSTNAME_ATAU_IP') // Hostname atau IP address server anda | contoh  ->setHostname('10.10.10.1') 
->set('remote_user', 'USER_SSH') // SSH user server anda | contoh ->set('remote_user', 'u1234567')
->set('port', 65002) // SSH port server anda, untuk kasus ini server yang saya gunakan menggunakan port custom | contoh ->set('remote_user', 65002)
->set('branch', 'master') // Git branch anda
->set('deploy_path', '~/PATH/SUB_PATH'); // Lokasi untuk menyimpan projek laravel pada server | contoh ->set('deploy_path', '~/public_html/api-deploy');

// HARUS DIGANTI SESUAI KEBUTUHAN ANDA

// Tasks

task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});

desc('Build assets');
task('deploy:build', [
    'npm:install',
]);

task('deploy', [
    'deploy:prepare',
    'deploy:secrets',       // Deploy secrets
    'deploy:vendors',
    'deploy:shared',
    'artisan:storage:link',
    'artisan:queue:restart',
    'deploy:publish',
    'deploy:unlock',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');

// Migrate database before symlink new release. Uncomment below code if you want to migrate after deploy

// before('deploy:symlink', 'artisan:migrate');
```

5. Membuat Github Workflow dengan menjalankan pada perintah sebagai berikut :
```
touch .github/workflows/master.yml
```

6. Silahkan ubah file master.yml dengan kode berikut : 
```
on:
  push:
    branches:
      - master

jobs:
  build-js-production:
    name: Build JavaScript/CSS for Production Server
    runs-on: ubuntu-latest
    if: github.ref == 'refs/heads/master'
    steps:
      - uses: actions/checkout@v1
      - name: NPM Build
        run: |
          npm install
          npm run build
      - name: Put built assets in Artifacts
        uses: actions/upload-artifact@v1
        with:
          name: assets
          path: public
          retention-days: 3
  deploy-production:
    name: Deploy Project to Production Server
    runs-on: ubuntu-latest
    needs: [ build-js-production ]
    if: github.ref == 'refs/heads/master'
    steps:
      - uses: actions/checkout@v1
      - name: Fetch built assets from Artifacts
        uses: actions/download-artifact@v1
        with:
          name: assets
          path: public
      - name: Setup PHP
        uses: shivammathur/setup-php@master
        with:
          php-version: '8.0'
          extension-csv: mbstring, bcmath
      - name: Composer install
        run: composer install -q --no-ansi --no-interaction --no-scripts --no-progress --prefer-dist
      - name: Setup Deployer
        uses: atymic/deployer-php-action@master
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
          ssh-known-hosts: ${{ secrets.SSH_KNOWN_HOSTS }}
      - name: Deploy to Development
        env:
          DOT_ENV: ${{ secrets.DOT_ENV_PRODUCTION }}
        run: php vendor/bin/dep deploy NAMA_REMOTE_HOST branch=master
```

Perlu ditekankan : 
Perintah dibawah ini merupakan proses untuk mengeksekusi deployer.php yang dilakukan oleh github action, NAMA_REMOTE HOST sesuaikan dengan konfigurasi deployer anda. NAMA_REMOTE_HOST diisikan bebas sesuai keinginan anda jika anda memberikan NAMA_REMOTE_HOST contoh ServerHostingku maka NAMA_REMOTE_HOST diubah menjadi ServerHostingku sehingga menjadi sebagai berikut
```
run: php vendor/bin/dep deploy ServerHostingku branch=master
```
```
host('ServerHostingku')
->setHostname('NAMA_HOSTNAME_ATAU_IP') // Hostname atau IP address server anda | contoh  ->setHostname('10.10.10.1') 
->set('remote_user', 'USER_SSH') // SSH user server anda | contoh ->set('remote_user', 'u1234567')
->set('port', 65002) // SSH port server anda, untuk kasus ini server yang saya gunakan menggunakan port custom | contoh ->set('remote_user', 65002)
->set('branch', 'master') // Git branch anda
->set('deploy_path', '~/PATH/SUB_PATH'); // Lokasi untuk menyimpan projek laravel pada server | contoh ->set('deploy_path', '~/public_html/api-deploy');
```

7. Menambahkan Credentials ke Github Secrets yang bisa anda akses melalui link
```
https://github.com/USERNAME/REPOSITORY/settings/secrets/actions/new
```
Kita membutuhkan 3 variabel yaitu : 

  - ${{ secrets.SSH_PRIVATE_KEY }}

  - ${{ secrets.SSH_KNOWN_HOSTS }}

  - ${{ secrets.DOT_ENV_PRODUCTION }}

3 Variabel ini dibutuhkan pada file master.yml untuk melakukan proses setup deployer dan deploy ke dalam server

```
- name: Setup Deployer
        uses: atymic/deployer-php-action@master
        with:
          ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
          ssh-known-hosts: ${{ secrets.SSH_KNOWN_HOSTS }}
      - name: Deploy to Development
        env:
          DOT_ENV: ${{ secrets.DOT_ENV_PRODUCTION }}
        run: php vendor/bin/dep deploy NAMA_REMOTE_HOST branch=master
```


8. Untuk mendapatkan SSH_PRIVATE_KEY dapat dilakukan cara sebagai berikut :
   - Buka terminal server anda kemudian jalankan perintah dibawah ini :
    ```
    ssh-keygen -t ed25519 -C "your_email@example.com"
    ```
   - Jika muncul tulisan "Enter a file in which to save the key," press Enter. Tekan enter saja sampai selesai.
   - Kemudian jalankan perintah dibawah ini :
  ```
  cat ~/.ssh/id_ed25519
  ```
  ![image](https://user-images.githubusercontent.com/64576201/196358242-6484c1ff-9bcb-41ef-a057-7f31457d0bb9.png)

    - Lalu copy semua kemudian tambahkan pada github secrets SSH_PRIVATE_KEY
  ![image](https://user-images.githubusercontent.com/64576201/196358495-3f281114-592f-4a8a-8b66-8d26dea06bdd.png)


9. Untuk mendapatkan SSH_KNOWN_HOSTS dapat dilakukan dengan cara sebagai berikut :
   - Buka terminal server anda kemudian jalankan perintah dibawah ini :
    ```
    ssh-keyscan -p 65002 IP_SERVER_ANDA
    ```
    IP_SERVER_ANDA ubah sesuai dengan ip server yang anda miliki
  ![image](https://user-images.githubusercontent.com/64576201/196354432-be41502a-a07e-44b4-beb5-1a841e1e1a36.png)

   - Lalu copy semua kemudian tambahkan pada github secrets SSH_KNOWN_HOSTS
  ![image](https://user-images.githubusercontent.com/64576201/196354509-d9cd6f06-e5c2-4f76-89c4-211f9b9471ac.png)
    
10. Untuk mengisi github secrets DOT_ENV_PRODUCTION anda dapat meng-copy semua isi .env pada projek laravel anda.
    ![image](https://user-images.githubusercontent.com/64576201/196354733-cef2ef84-6d0c-4362-866a-8cdad5069a6b.png)
    ![image](https://user-images.githubusercontent.com/64576201/196354836-6027cd08-3245-45f1-a7bb-09090948d1b0.png)

11. Silahkan melakukan perubahan pada repository anda maka Github Actions dan Deployer akan berjalan sebagaimana mestinya.
   ![image](https://user-images.githubusercontent.com/64576201/196372720-3b9d30f9-6381-4f6d-a449-7cc2546dfef9.png)
   ![image](https://user-images.githubusercontent.com/64576201/196372842-718f49be-8a04-423c-878e-ce301a59939d.png)

## Resources
- Laravel [Laravel](https://laravel.com/docs/9.x/installation)
- Deployer [Deployer](https://deployer.org/)

## Copyright
2022 [Yoga Meleniawan Pamungkas](https://github.com/yogameleniawan)   
