<?php

echo "We will guide you to config acme.sh. Sit and relax\n";
echo "Downloading acme.sh from github...\n";
system('cd '. __DIR__ .' && git clone https://github.com/Neilpang/acme.sh.git tmp && ./tmp/acme.sh --install --home ./acme.sh');