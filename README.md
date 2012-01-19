## About the Ratings plugin

Ratings allows registered users to rate an item using a scale that you determine. By default it is 1 to 5. 
It is inserted into your content using double tags and is therefore very flexible. It will work with nearly any 
module whether core or third-party. All it needs to work is a numerical id so that it can match the item with 
its rating.

Only registered users are allowed to rate and if they are not logged in they will be redirected to the login page. Upon login they 
will be sent back to the location they were trying to vote on. Only one rating is recorded per user regardless of how many times 
they click a rating. However if they choose a different rating on subsequent submissions their rating will be updated.

## Installation

Since this plugin requires a table to be created installation is 2 steps. 

* First upload ratings.php to your site's addons/shared_addons/plugins folder
* Copy this code: `{{ ratings:install }}` and place it somewhere in your site content (the home page for example)
* Refresh the page that you placed the code in on the last step. This will allow the plugin to create the table. You should receive a confirmation message.
* Now refer to the section below for instructions how to configure the tag that will allow the user to submit his rating and view previous ratings

## Using the Ratings tag

Now that you have used the `{{ ratings:install }}` code to create the required table you are ready to configure the tag.

There are 3 possible attributes that you can pass to the vote tag:

* item-id This attribute is required. Pass the id from the page, post, or whatever record the user is rating.
* module Optional. If you do not specify a module the module currently in use will be used.
* steps Optional. Defaults to 5. If you wanted to let the user choose on a scale of 1 to 10 you would specify 10. Or for a yes/no rating you could specify 2.

#### Basic Example

This is a basic 5 star rating configured for use on a Page:

	{{ ratings:vote item-id=page:id }}
		<li>
			<a href="{{ url }}">{{ theme:image file="{{ status }}.png" alt="vote {{item}}" }}</a>
		</li>
	{{ /ratings:vote }}

This expects your theme img folder to contain both on.png and off.png

#### More Advanced

This shows a 5 star rating and how many people have voted on the item:

	{{ ratings:vote item-id=page:id }}
		<li>
			<a href="{{ url }}">{{ theme:image file="{{ status }}.png" alt="vote {{item}}" }}</a>
		</li>
		{{ if item == 5 and total > 0 }}
		<li class="total">{{ total }} {{ if total > 1 }} people have {{ else }} person has {{ endif }} rated this</li>
		{{ endif }}
	{{ /ratings:vote }}

This expects your theme img folder to contain both on.png and off.png. 

The `{{ if item == 5 and total > 0 }}` statement simply checks to see if we are on the last item and if there is at least one rating. If so it adds another `<li>` that displays how many votes have been placed.

#### "YouTube Like" Slider Bar

It should be trivial to implement a slider that shows the number of "like" votes vs the number of "dislike" votes. I have not used this yet but I'd imagine the 
tags would look something like this:

	{{ ratings:vote item-id=page:id steps="2" }}
		{{ if item == 1 }}
			<a href="{{ url }}">Like</a>
			{{# Display the bar data and do something with it via css or js #}}
			{{ count }} of {{ total }}
		{{ else }}
			<a href="{{ url }}">Dislike</a>
		{{ endif }}
	{{ /ratings:vote }}

#### Available Fields

You may use any of these data tags within the double tag:

* {{ item }} The current item being displayed. Will be a number between 1 and 5 (or the maximum specified in the steps attribute)
* {{ url }} This is the generated url that places a vote for this item when clicked by a user
* {{ count }} A count of the votes that have been placed for this item
* {{ status }} Will be either "on" or "off" depending on whether this item is the highest rated, below it, or above it.
* {{ total }} The total ratings placed on all items. This is available on each item when looping over them