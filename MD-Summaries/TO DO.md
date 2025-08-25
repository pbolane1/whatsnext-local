### TO DO
Various ideas that I think of that I will need to address at some point

1. Session Timeout Implementation

2. Sticky Header Implementation

3. Progress Meter Smoothing

4. Vendor Rating System
    a. allow agents to rate a vendor
        i. number on star overlay for desktop/tablet. Allow them to reset to no rating. add Rating tool tip
        ii. mobile version shows 5 stars
        iii. store value to DB
    b. allow users to rate a vendor
        i. include tool tip
        ii. notify agent if rating has been added.  Maybe delay cron in case they make multiple changes within 10 minutes or so.
        iii. double check mobile implementation
    c. Allow agents to see 1. their own rating and 2. avergae of user rating (Avg of N Stars from Y ratings)
    d. Allow coordinators to see vendors average rating (Average of Agent Rating) (Average of User Rating) (Overall Average)

5. Color Presets Implementation
a. Keep in mind that I should build reuseable components
    b. Start with agent color picker
        i. Make sure it's mobile friendly as well
    c. Expand to Coordinator
        i. Ability for agent to reset to coordinator colors
        ii. Ability for agent to reset to default
        iii. If multiple coordinators, give agent multiple links to reset to each coordinator
        iv. if coordinator status is revoked, give agent the option to keep those colors or choose
    d. Expand to admin
    
6. Agent Footer Component

7. Scan site for mailicious PNG files
dynamic/images/agents/1687988801_hide7.png

8. Run Bug Bot?

9. Run Security Sweep bot?

10. nuke reference to buyer questionnaire in the footer
 It's in pages/users/index.php at line 75