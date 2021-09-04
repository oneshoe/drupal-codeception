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

Then, require `oneshoe/drupal-codeception:dev-master`.

## Configuring Codeception
To add to Codeception, make the following changes to your test suite files. The
OSDrupalAcceptance depends on several other modules. These need to be added to
the suite explicitly, and *in the correct order*, or they may not be found yet 
when they are needed.

### acceptance.suite.yml
    modules:
        enabled:
            - WebDriver
                # Refer to https://codeception.com/docs/modules/WebDriver for
                # configurarion instructions.
                # ...
            - DrupalDrush
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
