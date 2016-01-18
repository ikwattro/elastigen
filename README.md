## elastigen

### Elastic Search Data Generator

#### Usage:

Clone this library and install deps:

```bash
git clone git@github.com:ikwattro/elastigen
cd elastigen

composer install
```

You'll need to provide a `mapping` file (your ES mapping) and a `Providers` file representing to which generated data type a field in the mapping
is corresponding.

You can find an example in the resources folder.

For all providers available, you can check the README of the Faker library : https://github.com/fzaninotto/Faker#formatters

A host and an index name is also required, the last argument (number of generations) is optional and is defaulted to PHP_INT_MAX

```bash
php bin/app.php http://eshost:9200 testIndex resources/mapping.json resources/providers.json 1000
```

##### Warning

This code was made quickly for a quick sandbox data in Elastic, I don't provide support or guarantee improvements.


