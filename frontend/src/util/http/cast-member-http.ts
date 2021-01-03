import HttpResource from './http-resources';
import { httpVideo } from './index';

const castmemberhttp = new HttpResource(httpVideo, "cast_members");
export default castmemberhttp;