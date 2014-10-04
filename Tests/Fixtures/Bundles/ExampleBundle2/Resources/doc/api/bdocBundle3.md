### Delete a Note [DELETE]
Delete a single note

+ Response 204

+ Response 404

    + Headers

            Content-Type: application/json
            X-Request-ID: f72fc914
            X-Response-Time: 4ms

    + Body

            {
                "error": "Note not found"
            }

# Group Users
Group description

## User List [/users{?name,joinedBefore,joinedAfter,sort,limit}]
A list of users

+ Parameters

    + name (optional, string, `alice`) ... Search for a user by name
    + joinedBefore (optional, string, `2011-01-01`) ... Search by join date
    + joinedAfter (optional, string, `2011-01-01`) ... Search by join date
    + sort = `name` (optional, string, `joined`) ... Which field to sort by

        + Values
            + `name`
            + `joined`
            + `-joined`
            + `age`
            + `-age`
            + `location`
            + `-location`
            + `plan`
            + `-plan`

    + limit = `10` (optional, integer, `25`) ... The maximum number of users to return, up to `50`

+ Model

    + Headers

            Content-Type: application/json

    + Body

            [
                {
                    "name": "alice",
                    "image": "http://foo.com/alice.jpg",
                    "joined": "2013-11-01"
                },
                {
                    "name": "bob",
                    "image": "http://foo.com/bob.jpg",
                    "joined": "2013-11-02"
                }
            ]

    + Schema

            {
                "type": "array",
                "maxItems": 50,
                "items": {
                    "type": "object",
                    "properties": {
                        "name": {
                            "type": "string"
                        },
                        "image": {
                            "type": "string"
                        },
                        "joined": {
                            "type": "string",
                            "pattern": "\d{4}-\d{2}-\d{2}"
                        }
                    }
                }
            }
