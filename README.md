# Laravel CI/CD dengan Deployer dan Github Action

![CI CD](https://github.com/yogameleniawan/laravel-cicd-deployer/assets/64576201/692d27d8-c6bc-4447-91ae-5ea500757e7f)

### 🧭 Quick Navigation

-   **Language**: [🇮🇩 Bahasa Indonesia](#-bahasa-indonesia) | [🇬🇧 English](#-english-version)

---

## 🇮🇩 Bahasa Indonesia

### Daftar Isi

-   [Tutorial](#tutorial-indonesia)
-   [Sumber Daya](#sumber-daya--resources)
-   [Hak Cipta](#hak-cipta--copyright)

### Tutorial (Indonesia)

1.  **Install Laravel**
    ```bash
    composer create-project laravel/laravel example-app
    ```

2.  **Install Deployer**
    ```bash
    composer require --dev deployer/deployer
    ```

3.  **Inisialisasi Deployer**
    Jalankan perintah berikut dan pilih **PHP** saat diminta.
    ```bash
    vendor/bin/dep init
    ```

4.  **Ubah file `deploy.php`**
    Sesuaikan file `deploy.php` dengan konfigurasi server Anda.
    ```php
    <?php
    namespace Deployer;

    require 'recipe/laravel.php';
    require 'contrib/npm.php';

    // HARUS DIGANTI SESUAI KEBUTUHAN ANDA
    set('application', 'Nama Aplikasi Anda');
    set('repository', 'git@github.com:user/repo.git'); // URL SSH untuk clone Git
    set('bin/php', '/usr/bin/php8.2'); // Sesuaikan path PHP di server Anda
    // HARUS DIGANTI SESUAI KEBUTUHAN ANDA

    set('keep_releases', 5);
    add('shared_files', ['.env']);
    add('shared_dirs', ['storage']);
    add('writable_dirs', ['bootstrap/cache', 'storage']);

    // ----- Hosts -----
    // HARUS DIGANTI SESUAI KEBUTUHAN ANDA
    host('NAMA_REMOTE_ANDA') // Nama alias untuk server Anda (cth: production)
        ->setHostname('IP_SERVER_ANDA') // Hostname atau IP server
        ->set('remote_user', 'USER_SSH_ANDA') // User SSH di server
        ->set('port', 22) // Port SSH (default: 22)
        ->set('branch', 'master') // Branch Git yang akan di-deploy
        ->set('deploy_path', '~/public_html/nama-proyek'); // Path deploy di server
    // HARUS DIGANTI SESUAI KEBUTUHAN ANDA

    // ----- Tasks -----
    task('deploy:secrets', function () {
        file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
        upload('.env', get('deploy_path') . '/shared');
    });

    task('deploy', [
        'deploy:prepare',
        'deploy:secrets',
        'deploy:vendors',
        'deploy:shared',
        'artisan:storage:link',
        'deploy:publish',
    ]);

    after('deploy:failed', 'deploy:unlock');
    // before('deploy:symlink', 'artisan:migrate');
    ```

5.  **Buat File GitHub Workflow**
    ```bash
    mkdir -p .github/workflows
    touch .github/workflows/main.yml
    ```

6.  **Isi file `main.yml`**
    ```yaml
    name: Deploy to Production
    on:
      push:
        branches:
          - master # Atau branch utama Anda

    jobs:
      deploy:
        name: Deploy to Server
        runs-on: ubuntu-latest
        steps:
          - uses: actions/checkout@v3
          - name: Setup PHP
            uses: shivammathur/setup-php@v2
            with:
              php-version: '8.2'
          - name: Install Composer Dependencies
            run: composer install --prefer-dist --no-progress --no-dev
          - name: Setup Deployer
            uses: atymic/deployer-php-action@master
            with:
              ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
              ssh-known-hosts: ${{ secrets.SSH_KNOWN_HOSTS }}
          - name: Deploy
            env:
              DOT_ENV: ${{ secrets.DOT_ENV_PRODUCTION }}
            run: php vendor/bin/dep deploy NAMA_REMOTE_ANDA # Sesuaikan dengan nama host di deploy.php
    ```

7.  **Tambahkan Credentials ke GitHub Secrets**
    Akses `Settings > Secrets and variables > Actions` di repositori Anda dan tambahkan 3 secrets berikut:
    -   `SSH_PRIVATE_KEY`: Kunci privat SSH untuk mengakses server Anda.
    -   `SSH_KNOWN_HOSTS`: Sidik jari SSH dari server Anda.
    -   `DOT_ENV_PRODUCTION`: Seluruh isi file `.env` produksi Anda.

8.  **Cara Mendapatkan `SSH_PRIVATE_KEY`**
    Di terminal **server Anda**, jalankan:
    ```bash
    # Buat kunci baru jika belum ada
    ssh-keygen -t rsa -b 4096 -C "email-anda@example.com"
    
    # Tampilkan kunci privat untuk disalin
    cat ~/.ssh/id_rsa
    ```
    Salin seluruh output (termasuk `-----BEGIN...` dan `-----END...`) ke dalam secret `SSH_PRIVATE_KEY`.

9.  **Cara Mendapatkan `SSH_KNOWN_HOSTS`**
    Di terminal **lokal Anda**, jalankan:
    ```bash
    ssh-keyscan -p [PORT_SSH_ANDA] [IP_SERVER_ANDA]
    ```
    Salin outputnya ke dalam secret `SSH_KNOWN_HOSTS`.

10. **Selesai!**
    Sekarang, setiap kali Anda melakukan `push` ke branch `master`, GitHub Actions akan otomatis men-deploy proyek Anda.

---

## 🇬🇧 English Version

### Table of Contents

-   [Tutorial](#tutorial-english)
-   [Resources](#sumber-daya--resources)
-   [Copyright](#hak-cipta--copyright)

### Tutorial (English)

1.  **Install Laravel**
    ```bash
    composer create-project laravel/laravel example-app
    ```

2.  **Install Deployer**
    ```bash
    composer require --dev deployer/deployer
    ```

3.  **Initialize Deployer**
    Run the following command and select **PHP** when prompted.
    ```bash
    vendor/bin/dep init
    ```

4.  **Modify the `deploy.php` file**
    Adjust the `deploy.php` file with your server's configuration.
    ```php
    <?php
    namespace Deployer;

    require 'recipe/laravel.php';
    require 'contrib/npm.php';

    // MUST BE REPLACED WITH YOUR DETAILS
    set('application', 'Your Application Name');
    set('repository', 'git@github.com:user/repo.git'); // SSH URL to clone your Git repo
    set('bin/php', '/usr/bin/php8.2'); // Adjust the PHP path on your server
    // MUST BE REPLACED WITH YOUR DETAILS

    set('keep_releases', 5);
    add('shared_files', ['.env']);
    add('shared_dirs', ['storage']);
    add('writable_dirs', ['bootstrap/cache', 'storage']);

    // ----- Hosts -----
    // MUST BE REPLACED WITH YOUR DETAILS
    host('YOUR_REMOTE_NAME') // An alias for your server (e.g., production)
        ->setHostname('YOUR_SERVER_IP') // Server hostname or IP address
        ->set('remote_user', 'YOUR_SSH_USER') // SSH user on the server
        ->set('port', 22) // SSH port (default: 22)
        ->set('branch', 'master') // Git branch to deploy
        ->set('deploy_path', '~/public_html/project-name'); // Deployment path on the server
    // MUST BE REPLACED WITH YOUR DETAILS

    // ----- Tasks -----
    task('deploy:secrets', function () {
        file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
        upload('.env', get('deploy_path') . '/shared');
    });

    task('deploy', [
        'deploy:prepare',
        'deploy:secrets',
        'deploy:vendors',
        'deploy:shared',
        'artisan:storage:link',
        'deploy:publish',
    ]);

    after('deploy:failed', 'deploy:unlock');
    // before('deploy:symlink', 'artisan:migrate');
    ```

5.  **Create the GitHub Workflow File**
    ```bash
    mkdir -p .github/workflows
    touch .github/workflows/main.yml
    ```

6.  **Fill the `main.yml` file**
    ```yaml
    name: Deploy to Production
    on:
      push:
        branches:
          - master # Or your main branch

    jobs:
      deploy:
        name: Deploy to Server
        runs-on: ubuntu-latest
        steps:
          - uses: actions/checkout@v3
          - name: Setup PHP
            uses: shivammathur/setup-php@v2
            with:
              php-version: '8.2'
          - name: Install Composer Dependencies
            run: composer install --prefer-dist --no-progress --no-dev
          - name: Setup Deployer
            uses: atymic/deployer-php-action@master
            with:
              ssh-private-key: ${{ secrets.SSH_PRIVATE_KEY }}
              ssh-known-hosts: ${{ secrets.SSH_KNOWN_HOSTS }}
          - name: Deploy
            env:
              DOT_ENV: ${{ secrets.DOT_ENV_PRODUCTION }}
            run: php vendor/bin/dep deploy YOUR_REMOTE_NAME # Must match the host name in deploy.php
    ```

7.  **Add Credentials to GitHub Secrets**
    Go to `Settings > Secrets and variables > Actions` in your repository and add the following 3 secrets:
    -   `SSH_PRIVATE_KEY`: The private SSH key to access your server.
    -   `SSH_KNOWN_HOSTS`: The SSH fingerprint of your server.
    -   `DOT_ENV_PRODUCTION`: The entire content of your production `.env` file.

8.  **How to Get `SSH_PRIVATE_KEY`**
    In your **server's terminal**, run:
    ```bash
    # Create a new key if you don't have one
    ssh-keygen -t rsa -b 4096 -C "your-email@example.com"
    
    # Display the private key to copy it
    cat ~/.ssh/id_rsa
    ```
    Copy the entire output (including `-----BEGIN...` and `-----END...`) into the `SSH_PRIVATE_KEY` secret.

9.  **How to Get `SSH_KNOWN_HOSTS`**
    In your **local terminal**, run:
    ```bash
    ssh-keyscan -p [YOUR_SSH_PORT] [YOUR_SERVER_IP]
    ```
    Copy the output into the `SSH_KNOWN_HOSTS` secret.

10. **Done!**
    Now, every time you `push` to the `master` branch, GitHub Actions will automatically deploy your project.

---

### Sumber Daya / Resources

-   [Laravel](https://laravel.com/docs/9.x/installation)
-   [Deployer](https://deployer.org/)

### Hak Cipta / Copyright

2022 [Yoga Meleniawan Pamungkas](https://github.com/yogameleniawan)
