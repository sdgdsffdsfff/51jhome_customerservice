local empty = 0;

if redis.call("llen", "cs_line") == 0 then
    empty = 0;
end

redis.call("lpush", "cs_line", "1");

return empty