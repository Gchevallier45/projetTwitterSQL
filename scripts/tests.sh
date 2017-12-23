#!/bin/bash
SCRIPTPATH="$( cd "$(dirname "$0")" ; pwd -P )"
cd "$SCRIPTPATH/../"
source scripts/helpers/macros.sh
check_term


if [ -n "$LOCAL_DBPROJECT" ]; then
case "$1" in 
  user) f="user" ;;
  post) f="post" ;;
  hashtag) f="hashtag" ;;
  notification) f="notification" ;;
  * ) f="";;
esac
	bash scripts/populate_db.sh "test"
	for i in "tests/"*"${f}.php"; do
		echo $i
		echo -------
		phpunit --bootstrap "autoload.php" "$i"
	done
else
	vagrant ssh -c "export LOCAL_DBPROJECT=true; bash /vagrant/scripts/tests.sh"
fi
