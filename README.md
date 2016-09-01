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
