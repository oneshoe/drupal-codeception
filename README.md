# One Shoe Drupal Codeception extension
Extension to the Codeception toolset for Drupal testing.

## Adding to your project
To use, add the following repository definition to your composer.json:

    "repositories": [
        {
            "type": "composer",
            "url": "https://packages.office.oneshoe.nl/"
        }
    ]

Then, require `oneshoe/drupal-codeception-extension:dev-master`.

## Configuring Codeception
To add to Codeption, make the following changes to your test suite files.

### acceptance.suite.yml
    modules:
        enabled:
            - DrupalAcceptance
            - OSDrupalAcceptance:
                  rootUser: [name of the root (uid 1) user]
                  rootPassword: [password for that user]

## Development
For development you can use Lando. Start Lando with `lando start`. Then 
install a development environment by running run `lando composer install` and
`lando clean-install`. Run the test suite (verifying the Codeception module)
using `lando codecept` (this is just a way to running the regular codecept 
command within Lando).
