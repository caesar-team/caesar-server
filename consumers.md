#List of consumers

#### Mailing by a message queue
`bin/console rabbitmq:consumer -e prod -w -m 30 send_message`
idle timeout 30 sec
  `-m`, --messages[=MESSAGES]          Messages to consume [default: 0]
  `-l`, --memory-limit[=MEMORY-LIMIT]  Allowed memory for this process (MB)
  `-w`, --without-signals              Disable catching of system signals
  `-e`, --env=ENV                      The Environment name. [default: "dev"]