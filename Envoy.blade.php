@setup
require __DIR__.'/vendor/autoload.php';
$dotenv = new Dotenv\Dotenv(__DIR__);

$dotenv->load();
$dotenv->required(['REPOSITORY_URL', 'PROJECT_PATH', 'PHP_SERVICE_NAME'])->notEmpty();

$repo = getenv('REPOSITORY_URL');
$path = getenv('PROJECT_PATH');
$phpServiceName = getenv('PHP_SERVICE_NAME');

$date = (new DateTime())->format('YmdHis');
$env = isset($env) ? $env : "production";
$branch = isset($branch) ? $branch : "master";
$cleanup = isset($noCleanup) ? false : true;

$project = rtrim($path, '/');
$path = dirname($project);
$release = $path.'/__releases__/'.$date;
@endsetup

@servers(['web' => 'localhost'])

@story('deploy')
init
composer
migrate
cache
optimize
swap-symlink
cleanup
@endstory

@task('init')
{{-- Check if exist a symlink for the prject --}}
if [ ! -L "{{ $project }}" ]; then
{{-- Check if releases dir exists --}}
if [ ! -d "{{ dirname($release) }}" ]; then
{{-- Create a releases dir for zero downtime on deploy --}}
echo "crete releases directory"
mkdir -p "{{ dirname($release) }}"
mv "{{ $project }}" "{{ $release }}"
fi
else
{{-- Clone project --}}
echo "Deployment ({{ $date }}) started";
git clone {{ $repo }} --branch={{ $branch }} {{ $release }}
echo "Repository cloned";
fi

{{-- Create storage dir for persist data --}}
if [ ! -d "{{ $path }}/__storage__" ]; then
mv "{{ $release }}/storage" "{{ $path }}/__storage__"
mkdir -p "{{ $path }}/__storage__/public"
fi

{{-- Presist storage data --}}
rm -r "{{ $release }}/storage"
ln -s "{{ $path }}/__storage__" "{{ $release }}/storage"
ln -s "{{ $path }}/__storage__/public" "{{ $release }}/public/storage"
echo "Storage directory set up";

{{-- Update env file --}}
cp {{ $release }}/.env.example {{ $release }}/.env;
echo "Environment file set up";
@endtask

@task('composer')
{{-- Install composer dependencies --}}
echo 'Installing composer dependencies...'
cd {{ $release }};
composer install --no-interaction --no-dev --quiet;
echo 'Composer dependencies installed.'
@endtask

@task('migrate')
{{-- Run migrations --}}
echo 'Running migrations...'
php {{ $release }}/artisan migrate --env={{ $env }} --force --no-interaction;
@endtask

@task('cache')
{{-- Clear cache --}}
echo 'Clearing cache...'
php {{ $release }}/artisan view:clear;
php {{ $release }}/artisan cache:clear;
php {{ $release }}/artisan config:cache;
echo 'Cache cleared.';
@endtask

@task('optimize')
{{-- Optimize project --}}
php {{ $release }}/artisan optimize;
echo 'Project optimized.'
@endtask

@task('cleanup')
@if ($cleanup)
    {{-- search old projects that has not modified in the last day and remove 5 --}}
    find {{ dirname($release) }} -maxdepth 1 -name "20*" -mmin 1440 | head -n 5 | xargs rm -rf
    echo "Cleaned up old deployments";
@endif
@endtask

@task('swap-symlink')
{{-- Update symplink --}}
echo 'Updating symlink...'
ln -sfn "{{ $release }}" "{{ $project }}"
echo 'v.{{ $date }}' > {{ $release }}/public/storage/version.html
echo 'Deploy release path: {{ $release }}';
@endtask

@task('restart-php')
{{-- Restart PHP Service --}}
echo 'Restarting PHP service...'
sudo service {{ $phpServiceName }} restart
@endtask