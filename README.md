## How to use the ParkBark API

##### Basic use:
- Base endpoint is `/parks`

##### Sorting by proximity
- Default sorting is `ASC`
- Example: `parks?sort_order=DESC`

##### To filter by proximity:
- `/parks?loc=LAT,LNG<=Xmiles`
- Latitude and longitude in decimal format, no spaces
- Distance filter can be in miles or kilometers
- Fallback location filter is _45.5189220,-122.6793480<=50miles_ (Pioneer Courthouse Square)
- Example: `/parks?loc=45.5189220,-122.6793480<=5miles`

##### To filter by amenities:
- `/parks?amenities=TID`
- Amenities by TID:

   | Amenity         | TID            |
   | :-------------: | :-------------:|
   | Benches         |   1            |
   | Drinking Water  |   2            |
   | Fenced Area     |   3            |
   | Small Dogs Park |   4            |
   | Restrooms       |   5            |
   | Agility Course  |   6            |

- Falls back to _all_ if filter not included
- Example: `/parks?amenities=5`

##### Combining filters
- Use `&` to combine filter values
- Example: `/parks?loc=45.5189220,-122.6793480<=5miles&amenities=5`

#### POST Survey Responses
POST /entity/node HTTP/1.1
Host: parkbark-api.bfdig.com
Accept: application/json
Content-Type: application/hal+json
Authorization: Basic Og==
Cache-Control: no-cache

`{
  "_links": {
  	"type": {
  		"href":"http://parkbark-api.bfdig.com/rest/type/node/survey_responses"
  	},
  	"http://parkbark-api.bfdig.com/rest/relation/node/survey_responses/field_park_amenities": {
  		"href": "http://parkbark-api.bfdig.com/taxonomy/term/1?_format=hal_json"
  	}
  },
  "type":[{"target_id":"survey_responses"}],
  "title":[{"value":"testing"}],
  "field_notes":[{"value":"How are you?"}],
  "field_number_of_dogs":[{"value":5}],
  "field_device_id":[{"value": "abc123"}],
  "field_park_amenities": [
  	{"target_id": "2"},
  	{"target_id": "4"}
  ]

}`
