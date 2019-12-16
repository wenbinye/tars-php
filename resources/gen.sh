#!/bin/bash

dir=$(dirname $0)

# tars-gen -f -n 'wenbinye\tars\stat' -s StatF=tars.tarsstat.StatObj -t $dir/StatF.tars -o $dir/../src/stat
# tars-gen -f -n 'wenbinye\tars\report' -s ServerF=tars.tarsnode.ServerObj -t $dir/NodeF.tars -o $dir/../src/report
tars-gen -f -n 'wenbinye\tars\stat' -s ServerF=tars.tarsproperty.PropertyObj -t $dir/PropertyF.tars -o $dir/../src/stat
