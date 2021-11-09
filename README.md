# challenge_integrity

1) git clone https://github.com/lfrmonteiro99/challenge_integrity.git
2) cd challenge_integrity
3) docker-compose up -d --build
4) docker-compose exec php composer install
5) docker-compose exec php symfony console doctrine:migrations:migrate

### Run command
##### Arguments:
[-u|--url URL] [-t|--file_type [FILE_TYPE]] [-e|--fail_on_error [FAIL_ON_ERROR]] [-m|--scroll_to_element [SCROLL_TO_ELEMENT]] [-s|--selector [SELECTOR]] [-p|--full_page [FULL_PAGE]] [-l|--lazy_load [LAZY_LOAD]] [-W|--width [WIDTH]] [-H|--height [HEIGHT]]

##### To run the command to take screenshots:
-> docker-compose exec php php bin/console app:screenshot -u www.google.pt
####### only url is required to input as an argument

### See logs in web page
http://localhost:8080/
