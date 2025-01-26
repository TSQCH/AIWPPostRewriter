<?php

// Schedule cron job.
add_action('gpt_wp_post_rewriter_cron_job', 'gpt_wp_post_rewriter_process_posts');
function gpt_wp_post_rewriter_process_posts() {
    global $wpdb;

    $post_range = get_option('gpt_wp_post_rewriter_post_range', '1-100');
    list($start, $end) = explode('-', $post_range);

    $post_tag = get_option('gpt_wp_post_rewriter_post_tag', '');
    $post_type = get_option('gpt_wp_post_rewriter_post_type', 'post');
    $post_category = get_option('gpt_wp_post_rewriter_post_category', '');
    $frequency = get_option('gpt_wp_post_rewriter_frequency', 'hourly');

    // Base query
    $sql = "
    SELECT p.ID, p.post_content
    FROM {$wpdb->posts} p
    LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = 'ai_revamped'
    WHERE p.post_status = 'publish'
    AND p.ID BETWEEN %d AND %d
    AND (pm.meta_value IS NULL OR pm.meta_value = '0')
    ";

    // Add conditions for post type
    if (!empty($post_type)) {
        $sql .= " AND p.post_type = %s";
        $params[] = $post_type;
    }

    // Add conditions for post category
    if (!empty($post_category)) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tr.object_id = p.ID AND tt.taxonomy = 'category' AND tt.term_id = %d
        )";
        $params[] = $post_category;
    }

    // Add conditions for post tag
    if (!empty($post_tag)) {
        $sql .= " AND EXISTS (
            SELECT 1 FROM {$wpdb->term_relationships} tr
            INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
            WHERE tr.object_id = p.ID AND tt.taxonomy = 'post_tag' AND tt.term_id = %d
        )";
        $params[] = $post_tag;
    }

    // Add ordering and limit
    $sql .= " ORDER BY p.ID ASC LIMIT 0, 10";

    // Prepare the query with parameters
    $params = array_merge([$start, $end], $params);
    $query = $wpdb->prepare($sql, ...$params);

    echo $query. "<br>";

    // Execute the query
    $results = $wpdb->get_results($query);

    // Prepare the posts and post IDs arrays
    $posts = [];
    $post_ids = [];
    if ($results) {
        foreach ($results as $post) {
            $posts[] = trim($post->post_content);
            $post_ids[] = $post->ID;
        }
    }
    
    // Prepare the payload
    $payload = [
        'toolType' => 'article_generator',
        'post_ids' => $post_ids,
        'posts'    => $posts,
    ];

    echo '\n****************************************************\<br>';
    echo 'payload ==== <pre>'; print_r($payload); echo '</pre>'; //exit;
    echo '\n****************************************************\n';
    $response = gpt_wp_post_rewriter_lambda_function($payload);
    // $response = [
    //     "articles" => [
    //         "The Expansive Scope of Artificial Intelligence\n\nFocus Keyword: Artificial Intelligence\n\nAlternative Main Title: Artificial Intelligence: Unleashing Future Potential\n\nSection 1\n\nThe Expansive Scope of Artificial Intelligence\n\nArtificial Intelligence (AI) is not just a technological advancement; it is a transformative force that is reshaping the future of industries and societies worldwide. The scope of AI is vast, encompassing everything from automating routine tasks to revolutionizing complex decision-making processes. For business executives, mid-level managers, and entrepreneurs, understanding the potential of AI is crucial for staying competitive in today's rapidly evolving market. AI's ability to process and analyze large volumes of data with unprecedented speed and accuracy is unparalleled. This capability allows businesses to gain deeper insights into market trends, consumer behavior, and operational efficiencies, enabling them to make more informed and strategic decisions. Moreover, AI's adaptability means it can be tailored to suit the specific needs of different industries, whether it's enhancing customer service in retail, optimizing supply chains in manufacturing, or personalizing patient care in healthcare. The versatility of AI makes it an invaluable asset for businesses looking to innovate and grow.\n\nDriving Innovation Across Sectors\n\nAI's scope extends beyond traditional business applications, driving innovation across a wide range of sectors. In the healthcare industry, AI-powered tools are transforming diagnostics and treatment planning, leading to more accurate and timely interventions. For instance, AI algorithms can analyze medical images to detect anomalies that may be missed by the human eye, improving early detection rates for diseases such as cancer. In the finance sector, AI is revolutionizing risk management and fraud detection, providing more robust and secure financial systems. AI's ability to analyze transaction patterns and identify suspicious activities in real-time helps financial institutions protect their assets and maintain customer trust. In the automotive industry, AI is at the forefront of developing autonomous vehicles, promising safer and more efficient transportation solutions. These advancements are just the tip of the iceberg, as AI continues to push the boundaries of what is possible, opening up new opportunities for growth and development across various domains.\n\nEnhancing Customer Experiences\n\nOne of the most exciting aspects of AI is its potential to enhance customer experiences. Businesses are increasingly leveraging AI to deliver personalized and seamless interactions with their customers. AI-powered chatbots and virtual assistants are becoming commonplace, providing instant and accurate responses to customer queries, improving satisfaction and loyalty. These AI-driven solutions can handle a wide range of tasks, from answering frequently asked questions to processing orders and handling complaints, freeing up human agents to focus on more complex issues. Additionally, AI algorithms can analyze customer data to identify preferences and behaviors, enabling businesses to tailor their products and services to meet individual needs. This level of personalization not only enhances the customer experience but also drives sales and revenue growth. As AI technology continues to advance, businesses will have even more opportunities to create meaningful and engaging interactions with their customers, setting themselves apart in a competitive marketplace.\n\nSection 2\n\nAI's Role in Economic Growth and Development\n\nThe scope of AI extends to its significant impact on economic growth and development. Countries around the world, including the UAE, Dubai, Saudi Arabia, and Riyadh, are investing heavily in AI to diversify their economies and drive innovation. These nations recognize that AI has the potential to create new industries, generate employment, and enhance the overall quality of life for their citizens. By integrating AI into various sectors, these countries are positioning themselves as leaders in the global economy. For instance, Dubai's Smart City initiative aims to leverage AI and other advanced technologies to create a more efficient, sustainable, and livable city. This forward-thinking approach not only attracts foreign investment but also fosters a culture of innovation and entrepreneurship, driving economic growth and development. The strategic implementation of AI in these regions is a testament to their commitment to building a prosperous and sustainable future.\n\nEmpowering the Workforce\n\nAI's scope also includes its potential to empower the workforce. While some may fear that AI will lead to job displacement, the reality is that AI can augment human capabilities and create new opportunities for employment. By automating routine and repetitive tasks, AI frees up valuable time for employees to focus on more complex and creative work. This shift not only enhances productivity but also fosters a culture of innovation and continuous improvement within organizations. Business leaders must be proactive in preparing their workforce for this transition by investing in training and development programs. By equipping employees with the skills needed to thrive in an AI-driven world, businesses can ensure a smooth and successful integration of AI into their operations. Additionally, AI can facilitate collaboration and communication within teams, breaking down silos and promoting a more agile and responsive work environment. As AI technology continues to evolve, its role in empowering the workforce will become even more pronounced, driving innovation and growth across industries.\n\nShaping the Future of Business\n\nThe expansive scope of AI is shaping the future of business in profound ways. As AI technology continues to advance, businesses will have even more opportunities to innovate and grow. The integration of AI into business practices is not just a trend; it is a necessity for staying competitive in today's fast-paced market. By leveraging AI, businesses can gain a deeper understanding of their customers, optimize their operations, and drive revenue growth. The potential of AI is limitless, and its impact on the business world is only just beginning to be realized. As AI continues to evolve, it will undoubtedly play a central role in shaping the future of industries and societies worldwide. Business leaders who embrace AI and its potential will be well-positioned to lead their organizations into a prosperous and sustainable future. The journey towards an AI-driven future is an exciting one, full of opportunities for growth, innovation, and success.\n\n\n\n\n\n#ArtificialIntelligence #Innovation #FutureTech #BusinessGrowth #EconomicDevelopment #SmartCities #AIinBusiness #AIRevolution"
    //     ],
    //     "focus_keywords" => [
    //         "Artificial Intelligence"
    //     ]
    // ];
    echo 'response ==== <pre>'; print_r($response); echo "</pre>"; //exit;

    if (!empty($response['articles'])) {
        foreach ($post_ids as $index => $post_id) {
            $new_content = $response['articles'][$index] ?? '';
            $postArr = explode("\n\n", $new_content);
            // echo 'response ==== <pre>'; print_r($postArr); exit;
            $postTitle = $postArr[0];
            $focusKeyword = $response['focus_keywords'][$index] ?? '';
            $postTags = explode(" ",str_replace("#","",$postArr[count($postArr) - 1])); //str_replace("#","",$postArr[count($postArr) - 1]);

            $oldContent = get_post_field('post_content', $post_id);

            // Update post.
            wp_update_post([
                'ID' => $post_id,
                'post_content' => $new_content,
            ]);

            // Update meta.
            update_post_meta($post_id, 'rank_math_focus_keyword', $focusKeyword);
            update_post_meta($post_id, 'ai_revamped', true);
            update_post_meta($post_id, 'old_post_content', $oldContent);

            update_post_title($post_id, $postTitle);
            attach_tags_to_post($post_id, $postTags);
        }
    }
}

function update_post_title($post_id,$new_title){
    // Prepare the post data array
    $post_data = [
        'ID'         => $post_id,
        'post_title' => $new_title,
    ];

    // Update the post
    $result = wp_update_post($post_data);

    // Check for success or failure
    if (is_wp_error($result)) {
        // Handle errors
        error_log("Error updating post: " . $result->get_error_message());
        echo "Failed to update the post title.";
    } else {
        echo "Post title updated successfully!";
    }
}

function attach_tags_to_post($post_id, $tags) {
    echo 'attach_tags_to_post ==== <pre>'; print_r($tags); echo "</pre>";
    foreach ($tags as $tag_name) {
        // Check if the tag already exists
        $tag = term_exists($tag_name, 'post_tag');

        echo 'tag === <pre>'; print_r($tag); echo '</pre>';
        
        if (!$tag) {
            // If the tag doesn't exist, create it
            $new_tag = wp_insert_term($tag_name, 'post_tag');
            echo 'newtag === <pre>'; print_r($new_tag); echo '</pre>';
            
            if (is_wp_error($new_tag)) {
                // Handle errors during tag creation
                error_log("Error creating tag: " . $tag_name);
                continue;
            }

            $tag_id = $new_tag['term_id'];
        } else {
            // Use the existing tag ID
            $tag_id = $tag['term_id'];
        }

        // Attach the tag to the post
        echo "lass ====> " . $post_id . "====" . $tag_id . "====" . 'post_tag';
        wp_set_post_terms($post_id, $tag_name, 'post_tag', true);
    }
}