#List of crons

#### Clear obsolete data from message_history table
`bin/console app:clear-message-history -e prod`
##### period
0 23 * * *
`-e`, --env=ENV                      The Environment name. [default: "dev"]