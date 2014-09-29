FORMAT: 1A
HOST: https://api.mywebsite.com

# API Title
[Markdown](http://daringfireball.net/projects/markdown/syntax) **formatted** description.

## Subtitle
Also Markdown *formatted*. This also includes automatic "smartypants" formatting -- hooray!

> "A quote from another time and place"

Another paragraph. Code sample:

```http
Authorization: bearer 5262d64b892e8d4341000001
```

And some code with no highlighting:

```no-highlight
Foo bar baz
```

# Group Notes
Group description (also with *Markdown*)

## Note List [/notes]
Note list description

+ Even
+ More
+ Markdown

+ Model

    + Headers

            Content-Type: application/json
            X-Request-ID: f72fc914
            X-Response-Time: 4ms

    + Body

            [
                {
                    "id": 1,
                    "title": "Grocery list",
                    "body": "Buy milk"
                },
                {
                    "id": 2,
                    "title": "TODO",
                    "body": "Fix garage door"
                }
            ]
