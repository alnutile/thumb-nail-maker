## Iron ThumbnailMaker


Run locally

~~~
docker run --rm -v "$(pwd)":/worker -w /worker iron/images:php-5.6 sh -c "php /worker/workers/ThumbMaker.php -payload payload.json"
~~~

Get into the bash

~~~
docker run -it -v "$(pwd)":/worker -w /worker iron/images:php-5.6 /bin/bash
~~~

Set your .env


Via Curl

~~~
curl -H "Content-Type: application/json" -X POST -d '{"folder": "bundles/mock-project-1/requests/mock-request-1/compares/a"}' "https://worker-aws-us-east-1.iron.io/2/projects/IRON_PROJECT_ID/tasks/webhook?code_name=ThumbMaker&oauth=IRON_TOKEN_ID"
~~~