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

@task('deployment_option_cleanup')
    cd {{ dirname($release) }};
    @if ($cleanup)
        ls -t | head -3 | xargs rm -Rf
        echo "Cleaned up old deployments";
    @endif
@endtask

@task('swap-symlink')
    ln -sfn "{{ $release }}" "{{ $project }}"
@endtask