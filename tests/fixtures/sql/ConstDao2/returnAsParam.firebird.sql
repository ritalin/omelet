EXECUTE BLOCK RETURNS (value1 int, value2 int)
AS
BEGIN
    select 100 as value11, 19 as value2 from rdb$database into value1, value2;
    suspend;
END;
