<?php
declare(strict_types=1);

function products(): array
{
    $products = [
        ['id' => 'rose-garden', 'name' => 'Rose Garden', 'category' => 'romance', 'price' => 1680, 'image' => 'rose-garden.jpg', 'tag' => 'Best-loved', 'description' => 'Blush garden roses with fresh greenery. A lovely pick for birthdays, anniversaries, or just because.'],
        ['id' => 'peony-cloud', 'name' => 'Peony Cloud', 'category' => 'signature', 'price' => 2450, 'image' => 'peony.jpg', 'tag' => 'Seasonal', 'description' => 'Soft pink peonies with a few light, airy stems. Available while the season lasts.'],
        ['id' => 'tulip-morning', 'name' => 'Tulip Morning', 'category' => 'bright', 'price' => 1380, 'image' => 'tulip.jpg', 'tag' => 'Just arrived', 'description' => 'Pastel tulips with long, graceful stems. They open a little more each day.'],
        ['id' => 'wildflower-daydream', 'name' => 'Wildflower Daydream', 'category' => 'bright', 'price' => 1520, 'image' => 'wildflower.jpg', 'tag' => 'Florist pick', 'description' => 'A relaxed mix of daisies, cosmos, and whatever is looking good at the market.'],
        ['id' => 'sunlit-sunflowers', 'name' => 'Sunlit Sunflowers', 'category' => 'bright', 'price' => 1280, 'image' => 'sunflower.jpg', 'tag' => 'A bright one', 'description' => 'Golden sunflowers with chamomile and leafy stems. A cheerful hello for any day.'],
        ['id' => 'blush-and-bloom', 'name' => 'Blush & Bloom', 'category' => 'romance', 'price' => 1980, 'image' => 'blush.jpg', 'tag' => 'Customer favourite', 'description' => 'Pink roses and lisianthus in a soft, easygoing arrangement.'],
        ['id' => 'white-garden', 'name' => 'White Garden', 'category' => 'signature', 'price' => 1850, 'image' => 'white-garden.jpg', 'tag' => 'Simple & lovely', 'description' => 'Cream and white blooms with garden greens, arranged in a calm, classic palette.'],
        ['id' => 'rosy-posy', 'name' => 'Rosy Posy', 'category' => 'romance', 'price' => 1450, 'image' => 'rosy-posy.jpg', 'tag' => 'A small gesture', 'description' => 'A hand-tied bunch of rosy flowers, wrapped simply and ready to give.'],
        ['id' => 'petal-party', 'name' => 'Petal Party', 'category' => 'bright', 'price' => 1720, 'image' => 'petal-party.jpg', 'tag' => 'Full of colour', 'description' => 'Seasonal flowers in apricot, butter yellow, and pink. Each bunch is a little different.'],
        ['id' => 'ever-after', 'name' => 'Ever After', 'category' => 'signature', 'price' => 2650, 'image' => 'ever-after.jpg', 'tag' => 'The big bouquet', 'description' => 'A generous arrangement of premium roses and greenery for a day worth celebrating.'],
        ['id' => 'daisy-days', 'name' => 'Daisy Days', 'category' => 'bright', 'price' => 1180, 'image' => 'daisy.jpg', 'tag' => 'Easy favourite', 'description' => 'Fresh white daisies with sunny centres. A cheerful bunch for a kitchen table or a friend.'],
        ['id' => 'lavender-haze', 'name' => 'Lavender Haze', 'category' => 'signature', 'price' => 1780, 'image' => 'lavender.jpg', 'tag' => 'Soft colours', 'description' => 'Lilac-toned flowers and lavender sprigs in a gentle, cool palette.'],
        ['id' => 'crimson-rose-promise', 'name' => 'Crimson Rose Promise', 'category' => 'romance', 'price' => 1890, 'image' => 'crimson-rose-promise.jpg', 'tag' => 'New arrival', 'description' => 'A rich gathering of red roses with delicate white filler flowers and fresh greenery, wrapped in deep crimson. A timeless way to say you care.'],
        ['id' => 'ivory-elegance', 'name' => 'Ivory Elegance', 'category' => 'signature', 'price' => 2150, 'image' => 'ivory-elegance.jpg', 'tag' => 'New arrival', 'description' => 'Creamy ivory roses arranged with airy white blooms and soft greenery. An understated bouquet for thoughtful celebrations.'],
        ['id' => 'peach-sunset', 'name' => 'Peach Sunset', 'category' => 'romance', 'price' => 1880, 'image' => 'peach-sunset.jpg', 'tag' => 'New arrival', 'description' => 'Peach-toned roses in a warm, gentle palette with seasonal filler flowers and soft wrapping. Lovely for birthdays and thank-yous.'],
        ['id' => 'lavender-dream', 'name' => 'Lavender Dream', 'category' => 'signature', 'price' => 1890, 'image' => 'lavender-dream.jpg', 'tag' => 'New arrival', 'description' => 'Lavender roses and lilac hydrangea-style blooms make a soft, dreamy arrangement with a calm, graceful feel.'],
        ['id' => 'golden-hour-tulips', 'name' => 'Golden Hour Tulips', 'category' => 'bright', 'price' => 1480, 'image' => 'golden-hour-tulips.jpg', 'tag' => 'New arrival', 'description' => 'A cheerful bundle of golden yellow tulips with delicate white accents. A bright pick-me-up for any day.'],
        ['id' => 'coral-celebration', 'name' => 'Coral Celebration', 'category' => 'bright', 'price' => 1950, 'image' => 'coral-celebration.jpg', 'tag' => 'New arrival', 'description' => 'A lively mix of coral and orange roses with vivid gerbera-style blooms and greenery, made for happy occasions.'],
        ['id' => 'pink-carnation-cloud', 'name' => 'Pink Carnation Cloud', 'category' => 'bright', 'price' => 1550, 'image' => 'pink-carnation-cloud.jpg', 'tag' => 'New arrival', 'description' => 'Soft pink carnations gathered into a full, cloud-like bouquet with tiny white flowers and eucalyptus.'],
        ['id' => 'blue-horizon', 'name' => 'Blue Horizon', 'category' => 'signature', 'price' => 2050, 'image' => 'blue-horizon.jpg', 'tag' => 'New arrival', 'description' => 'Blue hydrangea-style blooms with ivory roses and small white accents, wrapped in a cool blue palette.'],
    ];
    $reviews = product_reviews();
    foreach ($products as &$product) {
        $product['reviews'] = $reviews[$product['id']] ?? [];
        $ratings = array_column($product['reviews'], 'rating');
        $product['rating'] = $ratings ? round(array_sum($ratings) / count($ratings), 1) : 0;
        $product['reviewCount'] = count($ratings);
    }
    unset($product);
    return $products;
}

function product_reviews(): array
{
    // These fictional notes are sample content for the storefront demo.
    return [
        'rose-garden' => [['name' => 'Mia R.', 'rating' => 5, 'comment' => 'The roses arrived open and lovely. They made the whole room smell sweet.'], ['name' => 'Nina C.', 'rating' => 5, 'comment' => 'A thoughtful bunch and the wrapping was simple and beautiful.']],
        'peony-cloud' => [['name' => 'Ally S.', 'rating' => 5, 'comment' => 'The peonies opened over a few days and looked even better by the weekend.'], ['name' => 'Bea L.', 'rating' => 4, 'comment' => 'Soft colours and a generous bunch. I would order these again.']],
        'tulip-morning' => [['name' => 'Tess M.', 'rating' => 5, 'comment' => 'Such happy colours on the breakfast table. The stems kept growing in the vase.'], ['name' => 'Cris A.', 'rating' => 4, 'comment' => 'Fresh tulips and careful wrapping. They were a lovely surprise.']],
        'wildflower-daydream' => [['name' => 'Jo P.', 'rating' => 5, 'comment' => 'It felt like bringing a little garden indoors. Every stem was different.'], ['name' => 'Rae D.', 'rating' => 5, 'comment' => 'Bright, relaxed, and exactly right for a thank-you gift.']],
        'sunlit-sunflowers' => [['name' => 'Kai V.', 'rating' => 5, 'comment' => 'The sunflowers made my mum smile the moment she opened the door.'], ['name' => 'Elle T.', 'rating' => 4, 'comment' => 'A cheerful bunch with lovely greenery. Great value for the size.']],
        'blush-and-bloom' => [['name' => 'Ivy G.', 'rating' => 5, 'comment' => 'The blush tones were even prettier in person. My friend loved them.'], ['name' => 'Mara Q.', 'rating' => 5, 'comment' => 'Arrived fresh and tied so neatly. A lovely anniversary gift.']],
        'white-garden' => [['name' => 'Sam E.', 'rating' => 5, 'comment' => 'Calm, classic colours and very fresh flowers. Beautiful on our dining table.'], ['name' => 'Lani F.', 'rating' => 4, 'comment' => 'A graceful arrangement that felt just right for a new home.']],
        'rosy-posy' => [['name' => 'Dani W.', 'rating' => 5, 'comment' => 'A sweet little bunch that brightened an ordinary Tuesday.'], ['name' => 'Ysa B.', 'rating' => 4, 'comment' => 'Simple wrapping, fresh blooms, and a lovely note from the studio.']],
        'petal-party' => [['name' => 'Nica J.', 'rating' => 5, 'comment' => 'So colourful and full of life. It looked just like a celebration.'], ['name' => 'Faye K.', 'rating' => 5, 'comment' => 'Every flower was a little different, which made it feel personal.']],
        'ever-after' => [['name' => 'Ari H.', 'rating' => 5, 'comment' => 'A generous arrangement and a real showstopper for the celebration.'], ['name' => 'Luz O.', 'rating' => 4, 'comment' => 'The roses were beautiful and the bouquet looked full and thoughtful.']],
        'daisy-days' => [['name' => 'Mika B.', 'rating' => 5, 'comment' => 'These daisies made our kitchen feel sunny all week.'], ['name' => 'Noel R.', 'rating' => 5, 'comment' => 'Fresh, cheerful, and just the right size for a small table.']],
        'lavender-haze' => [['name' => 'Cami N.', 'rating' => 5, 'comment' => 'The lilac colours were gentle and lovely. Such a peaceful arrangement.'], ['name' => 'Gia P.', 'rating' => 4, 'comment' => 'A thoughtful mix with a soft scent. It made a lovely birthday gift.']],
        // New-arrival bouquets: fictional sample notes for the storefront demo.
        'crimson-rose-promise' => [['name' => 'Mia R.', 'rating' => 5, 'comment' => 'The red roses looked rich and fresh. A beautiful anniversary bouquet.'], ['name' => 'Nina C.', 'rating' => 5, 'comment' => 'The white filler flowers and crimson wrapping made this bouquet extra special.'], ['name' => 'Alex P.', 'rating' => 4, 'comment' => 'Lovely arrangement with vibrant roses. A thoughtful gift.']],
        'ivory-elegance' => [['name' => 'Ally S.', 'rating' => 5, 'comment' => 'The ivory roses looked elegant and delicate.'], ['name' => 'Bea L.', 'rating' => 5, 'comment' => 'A beautiful arrangement for a simple celebration.'], ['name' => 'Tess M.', 'rating' => 4, 'comment' => 'Loved the neutral colours and soft, classic style.']],
        'peach-sunset' => [['name' => 'Jo P.', 'rating' => 5, 'comment' => 'The peach colours were warm and cheerful.'], ['name' => 'Rae D.', 'rating' => 4, 'comment' => 'Pretty mix of roses with lovely soft wrapping.'], ['name' => 'Cris A.', 'rating' => 4, 'comment' => 'A sweet bouquet that makes a thoughtful gift.']],
        'lavender-dream' => [['name' => 'Kai V.', 'rating' => 5, 'comment' => 'The purple flowers made such a dreamy bouquet.'], ['name' => 'Elle T.', 'rating' => 5, 'comment' => 'Beautiful combination of lavender roses and lilac blooms.'], ['name' => 'Ivy G.', 'rating' => 4, 'comment' => 'Lovely pastel arrangement with calm, graceful colours.']],
        'golden-hour-tulips' => [['name' => 'Mara Q.', 'rating' => 5, 'comment' => 'The yellow tulips looked bright and cheerful.'], ['name' => 'Dani W.', 'rating' => 5, 'comment' => 'A beautiful sunny bouquet. Perfect for a birthday gift.'], ['name' => 'Ysa B.', 'rating' => 4, 'comment' => 'Fresh-looking tulips with lovely yellow wrapping.']],
        'coral-celebration' => [['name' => 'Nica J.', 'rating' => 5, 'comment' => 'The colours are vibrant and absolutely beautiful.'], ['name' => 'Faye K.', 'rating' => 5, 'comment' => 'A perfect celebration bouquet. Loved the flower combination.'], ['name' => 'Ari H.', 'rating' => 5, 'comment' => 'The arrangement looks full, colourful, and festive.']],
        'pink-carnation-cloud' => [['name' => 'Luz O.', 'rating' => 5, 'comment' => 'The pink carnations looked soft and full.'], ['name' => 'Mika B.', 'rating' => 4, 'comment' => 'A very pretty pastel bouquet with delicate details.'], ['name' => 'Noel R.', 'rating' => 5, 'comment' => 'A gentle and elegant arrangement. Loved it.']],
        'blue-horizon' => [['name' => 'Cami N.', 'rating' => 5, 'comment' => 'The blue and white flowers look peaceful and elegant.'], ['name' => 'Gia P.', 'rating' => 5, 'comment' => 'Loved the cool blue colours and ivory roses.'], ['name' => 'Sam E.', 'rating' => 4, 'comment' => 'A beautiful bouquet with matching blue wrapping.']],
    ];
}
