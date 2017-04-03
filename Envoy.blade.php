@setup
    require __DIR__.'/vendor/autoload.php';
    $dotenv = new Dotenv\Dotenv(__DIR__);

    $dotenv->load();
    $dotenv->required(['DEPLOY_REPOSITORY', 'DEPLOY_PROJECT'])->notEmpty();

    $repo = getenv('DEPLOY_REPOSITORY');
    $path = getenv('DEPLOY_PROJECT');

    $date = (new DateTime())->format('YmdHis');
    $env = isset($env) ? $env : "production";
    $branch = isset($branch) ? $branch : "master";
    $cleanup = isset($cleanup) ? $cleanup : true;

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
    if [ ! -L "{{ $project }}" ]; then
        if [ ! -d "{{ dirname($release) }}" ]; then
            echo "crete releases directory"
            mkdir -p "{{ dirname($release) }}"
            mv "{{ $project }}" "{{ $release }}"
        fi
    else
        echo "Deployment ({{ $date }}) started";
        git clone {{ $repo }} --branch={{ $branch }} {{ $release }}
        echo "Repository cloned";
    fi

    if [ ! -d "{{ $path }}/__storage__" ]; then
        mv "{{ $release }}/storage" "{{ $path }}/__storage__"
        mkdir -p "{{ $path }}/__storage__/public"
    fi

    ln -s "{{ $path }}/__storage__" "{{ $release }}/storage"
    ln -s "{{ $path }}/__storage__/public" "{{ $release }}/public/storage"
    echo "Storage directory set up";

    cp {{ $release }}/.env.example {{ $release }}/.env;
    echo "Environment file set up";
@endtask

@task('composer')
    cd {{ $release }};
    echo 'Installing composer dependencies...'
    composer install --no-interaction --quiet --no-dev;
    echo 'Composer dependencies installed.'
@endtask

@task('migrate')
    php {{ $release }}/artisan migrate --env={{ $env }} --force --no-interaction;
@endtask

@task('cache')
    php {{ $release }}/artisan view:clear --quiet;
    php {{ $release }}/artisan cache:clear --quiet;
    php {{ $release }}/artisan config:cache --quiet;
    echo 'Cache cleared.';
@endtask

@task('optimize')
    php {{ $release }}/artisan optimize --quiet;
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
    ln -sfn "{{ $release }}" "{{ $project }}"
@endtask