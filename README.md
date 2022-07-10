Stickee test
==================

This project is built on symfony



## Prerequisite <a id="markdown-header-prerequisite"></a>

Project uses Docker, before continue, make sure Docker is installed on you host machine.

Once docker is installed please implement all
[post install steps](https://docs.docker.com/install/linux/linux-postinstall/)

## Build app <a id="markdown-header-build-app"></a>

Run the following command on your host machine in root directory to build the projects:

```shell
make build
```

This command will download, build all the needed images and run containers

## [Optional] Create docker-compose.override.yml <a name="setup-locally-docker-compose-override"></a>

If you want to specify ports, please create docker-compose.override.yml e.g:

```yaml
version: "3.9"

services:
  nginx:
    ports:
      -   target: 80
          published: 8080 # You can use any port here
          protocol: tcp
```

## Run a project <a id="markdown-header-run-a-project"></a>
Run the following command on your host machine:

```shell
make run
```

The command will start all services.

## Check if it works <a id="markdown-header-check-if-it-works"></a>
* [http://localhost:8447](http://localhost:8447) - API. PHP backend Application


To run all tests, run

```shell
make tests
```

