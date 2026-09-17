{
  description = "Meals on Wheels — pet food marketplace";

  inputs = {
    nixpkgs.url = "github:NixOS/nixpkgs/nixos-unstable";
    flake-utils.url = "github:numtide/flake-utils";
  };

  outputs = { self, nixpkgs, flake-utils }:
    flake-utils.lib.eachDefaultSystem (system:
      let
        pkgs = nixpkgs.legacyPackages.${system};

        php = pkgs.php84.withExtensions ({ enabled, all }:
          enabled ++ (with all; [
            bcmath
            gd
            intl
            pdo_mysql
            zip
          ]));
      in
      {
        devShells.default = pkgs.mkShell {
          name = "mow";

          packages = [
            php
            php.packages.composer
            pkgs.nodejs_22
            pkgs.mariadb
          ];

          shellHook = ''
            echo "Meals on Wheels"
            echo "  php      $(php -r 'echo PHP_VERSION;')"
            echo "  composer $(composer --version --no-ansi 2>/dev/null | head -1 | cut -d' ' -f3)"
            echo "  node     $(node --version)"
            echo
            echo "  composer install && php artisan migrate --seed"
            echo "  php artisan serve"
          '';
        };
      });
}
